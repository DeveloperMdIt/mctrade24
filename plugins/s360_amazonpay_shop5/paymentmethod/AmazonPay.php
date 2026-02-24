<?php declare(strict_types=1);

namespace Plugin\s360_amazonpay_shop5\paymentmethod;

use JTL\Cart\Cart;
use JTL\Cart\CartItem;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Events\Dispatcher;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Plugin\Payment\Method;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SessionController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SyncController;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Events;
use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Psr\Log\LoggerInterface;
use stdClass;

class AmazonPay extends Method {

    private const EXCLUDED_PRODUCT_ATTRIBUTE = 'exclude_amapay';

    private const MONTHLY_SUBSCRIPTION_LIMITS = [
        'AUD' => 1000,
        'CHF' => 1000,
        'DKK' => 10000,
        'EUR' => 1000,
        'GBP' => 1000,
        'HKD' => 10000,
        'JPY' => 100000,
        'NOK' => 10000,
        'NZD' => 1000,
        'SEK' => 10000,
        'USD' => 1000,
        'ZAR' => 10000
    ];

    /**
     * @var null|int $paymentMethodId
     */
    private $paymentMethodId;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param PluginInterface $plugin
     * @return string
     */
    public static function getModuleId(PluginInterface $plugin) {
        return 'kPlugin_' . $plugin->getID() . '_amazonpay';
    }

    public function __construct($moduleID, $nAgainCheckout = 0) {
        parent::__construct($moduleID, $nAgainCheckout);
        $result = Shop::Container()->getDB()->select('tzahlungsart', 'cModulId', $this->moduleID);
        if ($result) {
            $this->paymentMethodId = (int)$result->kZahlungsart;
        }
        $this->config = Config::getInstance();
    }

    public function isValidIntern(array $args_arr = []): bool {
        if (!$this->duringCheckout) {
            // somebody changed our semantics, but we need this to be 1
            return false;
        }

        return parent::isValidIntern($args_arr);
    }

    /**
     * We override the parent function from PaymentMethod because we absolutely DO NOT want to log every single decision not to render the buttons, unless we are in Debug mode. (Note we cannot use our JtlLoggerTrait because PaymentMethod defines a doLog method itself.
     *
     * Also the call to isValidIntern in the parent method is wrong - it uses invalid parameters.
     *
     * @param object $customer
     * @param Cart $cart
     * @return bool - true, if $customer with $cart may use Payment Method
     * @throws \InvalidArgumentException
     */
    public function isValid(object $customer, Cart $cart): bool {

        if (!$this->duringCheckout) {
            // somebody changed our semantics, but we need this to be 1
            return false;
        }

        $totalSum = $cart->gibGesamtsummeWaren(true);

        if ($this->getSetting('min_bestellungen') > 0) {
            if (isset($customer, $customer->kKunde) && $customer->kKunde > 0) {
                $res = Shop::Container()->getDB()->executeQueryPrepared(
                    'SELECT COUNT(*) AS cnt
                        FROM tbestellung
                        WHERE kKunde = :cid
                        AND (cStatus = :stp OR cStatus = :sts)',
                    [
                        'cid' => (int)$customer->kKunde,
                        'stp' => BESTELLUNG_STATUS_BEZAHLT,
                        'sts' => BESTELLUNG_STATUS_VERSANDT
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                $count = (int)$res->cnt;
                if ($count < $this->getSetting('min_bestellungen')) {
                    /** @var LoggerInterface $logger */
                    if (Compatibility::isShopAtLeast53()) {
                        $logger = Plugin::getInstance()->getLogger();
                    } else {
                        $logger = Shop::Container()->getLogService();
                    }
                    $logger->debug('Amazon Pay: Anzahl Mindestbestellungen nicht erreicht. Amazon Pay wird nicht angeboten.');
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($this->getSetting('min') > 0 && $totalSum <= $this->getSetting('min')) {
            // minimum set order value not reached
            return false;
        }

        if ($this->getSetting('max') > 0 && $totalSum >= $this->getSetting('max')) {
            // maximum set order value exceeded
            return false;
        }

        if (Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT) !== null && Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT) > $totalSum) {
            // minimal order value for the current customer group not reached
            return false;
        }

        if (!isset($cart->PositionenArr) || empty($cart->PositionenArr)) {
            // cart is empty
            return false;
        }

        if ($this->containsExcludedProducts($cart)) {
            // excluded products currently in cart
            return false;
        }

        if (empty($this->getConfiguredShippingMethodIds())) {
            // no shipping method configured
            return false;
        }

        return true;
    }

    public function containsExcludedProducts(Cart $cart) {
        foreach ($cart->PositionenArr as $pos) {
            if ($pos->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && null !== $pos->Artikel && $this->isExcludedProduct($pos->Artikel)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the given product or any of its parent categories has the attribute exclude_amapay set on it.
     * @param mixed|Artikel $product
     * @return bool
     */
    public function isExcludedProduct($product): bool {

        // Check product itself
        if (isset($product->FunktionsAttribute[self::EXCLUDED_PRODUCT_ATTRIBUTE]) || isset($product->AttributeAssoc[self::EXCLUDED_PRODUCT_ATTRIBUTE])) {
            return true;
        }

        if ($this->isVoucherProduct($product)) {
            return true;
        }

        // check product categories
        $excludedCategoryIds = $this->getExcludedCategoryIds();

        if (isset($product->kArtikel)) {
            $productCategories = Shop::Container()->getDB()->selectAll('tkategorieartikel', ['kArtikel'], [$product->kArtikel]);
            if (!empty($productCategories)) {
                foreach ($productCategories as $productCategory) {
                    if (\in_array((int)$productCategory->kKategorie, $excludedCategoryIds, true)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Gets all excluded categories.
     */
    public function getExcludedCategoryIds() {
        if (Shop::has('lpaExcludedCategoryIds')) {
            return Shop::get('lpaExcludedCategoryIds');
        }

        $stmt = 'SELECT * FROM tkategorieattribut tka, tkategorie tk WHERE tka.kKategorie = tk.kKategorie AND tka.cName = "exclude_amapay" AND tka.cWert = "1"';
        $excludedCategories = Shop::Container()->getDB()->executeQueryPrepared($stmt, [], ReturnType::ARRAY_OF_OBJECTS);
        if (empty($excludedCategories)) {
            Shop::set('lpaExcludedCategoryIds', []);
            return [];
        }

        $excludedCategoryIds = [];
        foreach ($excludedCategories as $category) {
            $excludedCategoryIds[] = (int)$category->kKategorie;
            $excludedSubcategories = Shop::Container()->getDB()->executeQueryPrepared('SELECT * FROM tkategorie WHERE lft > :lft AND rght < :rght', ['lft' => $category->lft, 'rght' => $category->rght], ReturnType::ARRAY_OF_OBJECTS);
            if (!empty($excludedSubcategories)) {
                foreach ($excludedSubcategories as $subcategory) {
                    $excludedCategoryIds[] = (int)$subcategory->kKategorie;
                }
            }
        }

        $excludedCategoryIds = array_unique($excludedCategoryIds);
        Shop::set('lpaExcludedCategoryIds', $excludedCategoryIds);
        return $excludedCategoryIds;

    }

    /**
     * Returns all the ids of all configured shipping methods for the given country (or all, if no country is given)
     * @param string $countryIso
     * @return array the ids of the shipping methods for the given country, or all if no country is given, or empty array if none is found
     */
    public function getConfiguredShippingMethodIds(string $countryIso = ''): array {
        $res = $this->getConfiguredShippingMethods($countryIso);
        $res = array_map(function ($elem) {
            return (int)$elem->kVersandart;
        }, $res);
        return array_unique($res);
    }

    /**
     * Returns a list of configured shipping methods for a given country, if defined, or all.
     */
    private function getConfiguredShippingMethods(string $countryIso = ''): array {
        $sql = 'SELECT * FROM tversandart v, tversandartzahlungsart vz, tzahlungsart z WHERE vz.kZahlungsart = z.kZahlungsart AND vz.kVersandart = v.kVersandart AND z.cModulId = :cModulId';
        $params = ['cModulId' => $this->moduleID];
        if (!empty($countryIso)) {
            $sql .= ' AND v.cLaender LIKE :iso';
            $params['iso'] = '%' . $countryIso . '%';
        }
        $res = Shop::Container()->getDB()->executeQueryPrepared($sql, $params, ReturnType::ARRAY_OF_OBJECTS);
        if (empty($res)) {
            return [];
        }
        // Fire our own Hook to enable other plugins to manipulate the list of shipping methods.
        Dispatcher::getInstance()->fire(Events::AVAILABLE_SHIPPING_METHODS, ['shippingMethods' => &$res]);
        return $res;
    }

    /**
     * @return int
     */
    public function getPaymentMethodId(): int {
        return (int)$this->paymentMethodId;
    }

    /**
     * Adds an incoming payment - however, beforehand we check if we already added a payment for the given transaction/chinweis and do not add anything if that is the case.
     * @param \JTL\Checkout\Bestellung $order
     * @param Object $payment
     * @return $this
     */
    public function addIncomingPayment(Bestellung $order, object $payment) {
        $transactionId = $payment->cHinweis;
        $orderId = $order->kBestellung;
        $test = Shop::Container()->getDB()->selectAll('tzahlungseingang', ['kBestellung', 'cHinweis'], [$orderId, $transactionId]);
        if (empty($test)) {
            parent::addIncomingPayment($order, $payment);
        }
        return $this;
    }

    public function isOrderPaidCompletely(Bestellung $order): bool {
        $orderTotal = $order->fGesamtsumme;
        $incomingPayments = Shop::Container()->getDB()->selectAll('tzahlungseingang', ['kBestellung'], [$order->kBestellung]);
        if (!\is_array($incomingPayments)) {
            return false;
        }
        $sumOfPayments = array_reduce($incomingPayments, function ($carry, $item) {
            $carry += (float)$item->fBetrag;
            return $carry;
        }, 0.0);

        return $sumOfPayments >= $orderTotal;
    }

    /**
     * This method is called when an order gets cancelled by the JTL Wawi. (Storno)
     *
     * @param int $kBestellung
     * @param bool $bDelete
     * @return $this
     * @throws \JTL\Exceptions\CircularReferenceException
     * @throws \JTL\Exceptions\ServiceNotFoundException
     */
    public function cancelOrder(int $kBestellung, bool $bDelete = false) {
        // Let the parent class do its thing
        parent::cancelOrder($kBestellung, $bDelete);

        // ... and we have to cancel the order against Amazon Pay, too.
        // (Note: This might not be possible, actually, for example if immediate capture was done. In that case, the amount has to be refunded instead.)
        try {
            $syncController = new SyncController(Plugin::getInstance());
            $syncController->handleOrderCanceled((int)$kBestellung);
        } catch (\Exception $e) {
            /** @var LoggerInterface $logger */
            if (Compatibility::isShopAtLeast53()) {
                $logger = Plugin::getInstance()->getLogger();
            } else {
                $logger = Shop::Container()->getLogService();
            }
            $logger->error('Amazon Pay: Failed to cancel order against Amazon Pay with Exception: ' . $e->getMessage() . ' (' . $e->getCode() . ')');
        }

        return $this;
    }


    /**
     * HandleAdditional is called after the payment selection.
     *
     * If this is called on Amazon Pay, we should do a redirect to the Amazon Pay checkout iff we are NOT displayed within the regular payment methods.
     * Otherwise, the APB (additional payment button) mode begins here - we are fine with skipping ahead, because we don't do checks before order confirmation.
     *
     * @param array $aPost_arr
     * @return bool|void
     */
    public function handleAdditional(array $aPost_arr): bool {
        if (Request::isAjaxRequest()) {
            return true;
        }

        if ($this->config->isHidePaymentMethod()) {
            // We are currently not enabled as a regular payment method, so we have to send the user to our own checkout or back to payment method selection such that they can select a different method.

            // IMPORTANT: We will not redirect the user to our own checkout IFF he came here via "quick checkout" (this is the case when wk is 1), instead we redirect him to the shipping / payment page (this also prevents an endless loop / unreachability of checkout)
            if (isset($_REQUEST['wk']) && (int)$_REQUEST['wk'] === 1) {
                header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php', true, true) . '?editZahlungsart=1');
                exit();
            }

            // try to redirect the user to our checkout page
            // Also make sure to disable ourselves as payment method (temporarily) or the customer can not undo this selection and will end up in a loop
            unset($_SESSION['Zahlungsart'], $_SESSION['AktiveZahlungsart']);
            header('Location: ' . JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CHECKOUT), true, 303);
            exit();
        }
        return true;
    }

    /**
     * This function is a mystery.
     * It is called in the following circumstances:
     *
     * When ENTERING the payment method selection FROM the confirmation step.
     * When ENTERING the checkout from anywhere BUT with the payment method already selected.
     * NOT When going from payment method selection TO the confirmation step.
     * @return bool|void
     */
    public function validateAdditional(): bool {
        if ($this->config->isHidePaymentMethod()) {
            // we are not enabled as regular payment method, therefore by default we are not valid within the regular checkout.
            return false;
        }
        return true;
    }

    /**
     * Sets the order status to paid by setting dBezahltDatum, HOWEVER it does not change the order status if the order is in a status "beyond" paid.
     * Note that the Wawi can set this information, too, if the merchant manually adds a payment (e.g. when manually setting payments or using the payment sync of the wawi instead of relying on the plugin to add incoming payments)
     *
     * @param Bestellung $order
     * @return $this
     */
    public function setOrderStatusToPaid(Bestellung $order) {
        /** @var Bestellung $order */
        $orderId = (int)$order->kBestellung;
        $_upd = new  \stdClass();
        $changed = false;
        if ((int)$order->cStatus === BESTELLUNG_STATUS_OFFEN || (int)$order->cStatus === BESTELLUNG_STATUS_IN_BEARBEITUNG) {
            $_upd->cStatus = BESTELLUNG_STATUS_BEZAHLT;
            $changed = true;
        }
        // To consider: do we want to update the date field with every payment? or only for the first one? Currently we only set this on the first time this method is called
        if (empty($order->dBezahltDatum) || $order->dBezahltDatum === '0000-00-00') {
            $_upd->dBezahltDatum = 'NOW()';
            $changed = true;
        }
        if ($changed) {
            Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $orderId, $_upd);
        }
        return $this;
    }

    /**
     * Returns an array of ISO codes of the countries that this payment method is configured for.
     */
    public function getShippableCountries(): array {
        $result = [];
        $shippingMethods = $this->getConfiguredShippingMethods();
        if (\is_array($shippingMethods) && \count($shippingMethods) > 0) {
            $collectedIsos = [];
            foreach ($shippingMethods as $shippingMethod) {
                if (null !== $shippingMethod->cLaender) {
                    $collectedIsos[] = explode(' ', trim($shippingMethod->cLaender)); // "$shippingMethod->cLaender" is a text field with two letter ISO codes, separated by space}
                }
            }
            if (\count($collectedIsos) > 0) {
                $result = array_merge([], ...$collectedIsos);
            }
        }
        return array_unique($result);
    }

    /**
     * Controls if this method is displayed in the payment method selection during normal checkout.
     * @return bool
     */
    public function isSelectable(): bool {
        return parent::isSelectable() && !$this->config->isHidePaymentMethod() && Currency::getInstance()->isSupportedCurrency(Frontend::getCurrency()->getCode());
    }


    public function isCustomerGroupValid(): bool {
        $paymentMethod = Shop::Container()->getDB()->select('tzahlungsart', 'kZahlungsart', $this->getPaymentMethodId());
        if ($paymentMethod && !empty($paymentMethod->cKundengruppen)) {
            $customerGroup = Frontend::getCustomerGroup();
            if ($customerGroup !== null) {
                $customerGroupId = $customerGroup->getID();
                if ($customerGroupId > 0 && mb_stripos($paymentMethod->cKundengruppen, ';' . $customerGroupId . ';') === false) {
                    return false;
                }
            }
        }
        return true;
    }

    public function preparePaymentProcess(Bestellung $order): void {
        // At this point we can assume that we are in APB (additional payment button) mode and the user has confirmed the order.
        // Our goal here is: Redirect the user to Amazon Pay, including payment info and their delivery address, then have the user return to the regular return controller which will finish the order on success and then send the user to bestellabschluss.php?i=...

        // Save the order to the session so it survives the redirect
        SessionController::set(SessionController::KEY_APB_ORDER, $order);

        // Save the post array to be able to carry over Comments of the customer
        SessionController::set(SessionController::KEY_CONFIRM_POST_ARRAY, $_POST);

        // Save the checksum of the basket to be able to compare it on return.
        SessionController::set(SessionController::KEY_CART_CHECKSUM, Cart::getChecksum(Frontend::getCart()));

        // Fire Event if anybody wants to intervene
        Dispatcher::getInstance()->fire(Constants::EVENT_PREPARE_PAYMENT_PROCESS);

        // Redirect to our own redirection page.
        header('Location: ' . JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_APB_REDIRECT), true, 303);
        exit();
    }

    /**
     * Checks if a subscription can be used on express buying level.
     *
     * This is, generally speaking, the case if the cart is empty.
     */
    public function isExpressSubscriptionPossible(Artikel $product): bool {
        if ($this->config->getSubscriptionMode() !== Config::SUBSCRIPTION_MODE_ACTIVE) {
            return false; // subscriptions are not enabled
        }
        $total = $product->Preise->fVKBrutto;
        $currency = Frontend::getCurrency();
        $currencyCode = mb_strtoupper($currency->getCode());
        if (!Currency::getInstance()->isSupportedCurrency($currencyCode) || !isset(self::MONTHLY_SUBSCRIPTION_LIMITS[$currencyCode]) || self::MONTHLY_SUBSCRIPTION_LIMITS[$currencyCode] < $total) {
            return false; // current currency is either not supported or we are beyond the monthly subscription limits - note that the limit might actually be a lot smaller depending on the chosen interval, but this limit here always applies if no other limit does.
        }
        // subscriptions are also not possible for products on sale
        if ($product->cAktivSonderpreis === 'Y') {
            return false;
        }
        $cart = Frontend::getCart();
        if ($cart->posTypEnthalten(C_WARENKORBPOS_TYP_ARTIKEL)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_GUTSCHEIN)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_GRATISGESCHENK)) {
            return false;
        }
        if ($this->isStoreCreditUsed()) {
            return false; // we cannot handle using store credit
        }
        if (!$this->config->isSubscriptionGlobalActive() && !isset($product->FunktionsAttribute[$this->config->getSubscriptionFunctionalAttributeInterval()])) {
            return false; // subscriptions are only enabled on product basis but this product does not have the required functional attribute set
        }
        if ($this->isVoucherProduct($product)) {
            return false; // voucher products cannot be bought in subscriptions
        }
        return true;
    }

    /**
     * Checks if the cart allows for subscription buying.
     * Note: This function does NOT check the actually available intervals!
     */
    public function isSubscriptionPossibleForCart(): bool {
        if ($this->config->getSubscriptionMode() !== Config::SUBSCRIPTION_MODE_ACTIVE) {
            return false;
        }
        $cart = Frontend::getCart();
        $total = $cart->gibGesamtsummeWaren(true, false);
        $currency = Frontend::getCurrency();
        $currencyCode = mb_strtoupper($currency->getCode());
        $total = $currency->getConversionFactor() * $total; // Note that gibGesamtsummeWaren always returns values in the default currency with factor 1
        if (!Currency::getInstance()->isSupportedCurrency($currencyCode) || !isset(self::MONTHLY_SUBSCRIPTION_LIMITS[$currencyCode]) || self::MONTHLY_SUBSCRIPTION_LIMITS[$currencyCode] < $total) {
            return false; // current currency is either not supported or we are beyond the monthly subscription limits - note that the limit might actually be a lot smaller depending on the chosen interval, but these limits apply to "one time charges for recurring payments"
        }
        // check position types
        if ($cart->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_GUTSCHEIN)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            || $cart->posTypEnthalten(C_WARENKORBPOS_TYP_GRATISGESCHENK)) {
            return false;
        }
        if ($this->isStoreCreditUsed()) {
            return false; // we cannot handle using store credit
        }
        // check if all products in the cart (or our global config) enabled subscriptions
        if (!$this->config->isSubscriptionGlobalActive()) {
            foreach ($cart->PositionenArr as $cartItem) {
                if ((int)$cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && isset($cartItem->Artikel) && empty($cartItem->Artikel->FunktionsAttribute[$this->config->getSubscriptionFunctionalAttributeInterval()])) {
                    return false; // product in cart position has no interval set via functional attribute,
                }
            }
        }
        foreach ($cart->PositionenArr as $cartItem) {
            // check if any of the products has a special price
            if ((int)$cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && isset($cartItem->Artikel) && $cartItem->Artikel->cAktivSonderpreis === 'Y') {
                return false; // products with special prices cannot be bought via subscription
            }
            // check if the product is a voucher
            if ((int)$cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && isset($cartItem->Artikel) && $this->isVoucherProduct($cartItem->Artikel)) {
                return false;
            }
        }
        return true;
    }

    public function getPossibleSubscriptionIntervalsForCart(): array {
        $intervalArrays = [];
        $cart = Frontend::getCart();
        foreach ($cart->PositionenArr as $cartItem) {
            if ($cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $product = $cartItem->Artikel ?? null;
                if ($product !== null) {
                    $intervalArrays[] = $this->getPossibleSubscriptionIntervalsForProduct($product);
                }
            }
        }
        // Calculate charge limits, too! (I.e. carts with a sum beyond that cannot be bought as a subscription in any case)
        return $this->reduceIntervalsToChargeLimits(Interval::intersect($intervalArrays), $cart->gibGesamtsummeWaren(true, false), true);
    }

    public function getPossibleSubscriptionIntervalsForProduct(Artikel $product): array {
        $result = [];
        $intervalsString = '';
        if ($this->config->isSubscriptionGlobalActive()) {
            $intervalsString = $this->config->getSubscriptionGlobalInterval();
        }
        $functionalAttributeName = $this->config->getSubscriptionFunctionalAttributeInterval();
        if (isset($product->FunktionsAttribute[$functionalAttributeName])) {
            $intervalsString = $product->FunktionsAttribute[$functionalAttributeName];
        }
        $intervalStrings = explode(',', $intervalsString);
        foreach ($intervalStrings as $intervalString) {
            $interval = Interval::fromString($intervalString);
            if ($interval !== null) {
                $result[] = $interval;
            }
        }
        // Calculate charge limits, too! (I.e. products with a price beyond that cannot be bought as a subscription in any case)
        return $this->reduceIntervalsToChargeLimits($result, $product->Preise->fVKBrutto, false);
    }


    public function getSubscriptionDiscountRateForCart(): float {
        if(!$this->config->isSubscriptionDiscountFeatureEnabled() || $this->config->getSubscriptionDiscountMode() === Config::SUBSCRIPTION_DISCOUNT_MODE_INACTIVE) {
            return 0.0;
        }
        $absoluteDiscount = $this->getAbsoluteSubscriptionDiscountForCart();
        $cart = Frontend::getCart();
        $productPositionSumGross = 0;
        foreach ($cart->PositionenArr as $cartItem) {
            if ($cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $product = $cartItem->Artikel ?? null;
                if ($product !== null) {
                    $productPositionSumGross += Tax::getGross($cartItem->fPreis * $cartItem->nAnzahl, CartItem::getTaxRate($cartItem), 4);
                }
            }
        }
        return ($absoluteDiscount / $productPositionSumGross) * 100.0;
    }

    /**
     * Returns the absolute position discount
     * @return float
     */
    public function getAbsoluteSubscriptionDiscountForCart(): float {
        if(!$this->config->isSubscriptionDiscountFeatureEnabled() || $this->config->getSubscriptionDiscountMode() === Config::SUBSCRIPTION_DISCOUNT_MODE_INACTIVE) {
            return 0.0;
        }
        $cart = Frontend::getCart();
        $positionDiscounts = [];
        foreach ($cart->PositionenArr as $index => $cartItem) {
            if ($cartItem->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $product = $cartItem->Artikel ?? null;
                if ($product !== null) {
                    $productDiscountRate = $this->getSubscriptionDiscountRateForProduct($product);
                    if($productDiscountRate > 0) {
                        $positionDiscounts[$index] = Tax::getGross($cartItem->fPreis * $cartItem->nAnzahl, CartItem::getTaxRate($cartItem), 4) * ($productDiscountRate / 100);
                    }
                }
            }
        }
        return array_sum($positionDiscounts);
    }

    public function getSubscriptionDiscountRateForProduct(Artikel $product): int {
        if(!$this->config->isSubscriptionDiscountFeatureEnabled() || $this->config->getSubscriptionDiscountMode() === Config::SUBSCRIPTION_DISCOUNT_MODE_INACTIVE) {
            return 0;
        }
        $result = 0;
        if ($this->config->getSubscriptionDiscountMode() === Config::SUBSCRIPTION_DISCOUNT_MODE_GLOBAL) {
            $result = min(100, max(0, $this->config->getSubscriptionDiscountGlobal()));
        }
        // Functional attribute always overrides global
        $functionalAttributeName = $this->config->getSubscriptionDiscountAttribute();
        if(!empty($functionalAttributeName) && !empty($product->FunktionsAttribute[$functionalAttributeName])) {
            $result = min(100, max(0, (int)$product->FunktionsAttribute[$functionalAttributeName]));
        }
        return $result;
    }

    /**
     * @param array $intervals
     * @param float $singleChargeAmount - this amount is in DEFAULT currency
     * @param bool $needsCurrencyConversion
     * @return array
     */
    protected function reduceIntervalsToChargeLimits(array $intervals, float $singleChargeAmount, bool $needsCurrencyConversion = false) {
        $result = [];
        $currency = Frontend::getCurrency();
        $currencyCode = $currency->getCode();
        if (empty($currencyCode) || !array_key_exists($currencyCode, self::MONTHLY_SUBSCRIPTION_LIMITS)) {
            // Could not determine currency or currency has no defined limits.
            return [];
        }
        foreach ($intervals as $interval) {
            /** @var Interval $interval */
            $monthlyAmount = max($interval->getEstimatedMonthlyOccurrence(), 1.0) * $singleChargeAmount; // for the purpose of this, even if the occurence is lower than 1, the single charge must not exceed the limit
            if($needsCurrencyConversion) {
                $monthlyAmount *= $currency->getConversionFactor();
            }
            if ($monthlyAmount <= self::MONTHLY_SUBSCRIPTION_LIMITS[$currency->getCode()]) {
                $result[] = $interval;
            }
        }
        return $result;
    }

    protected function isStoreCreditUsed(): bool {
        return isset(
                $_SESSION['Bestellung']->GuthabenNutzen,
                $_SESSION['Bestellung']->fGuthabenGenutzt,
                $_SESSION['Kunde']->fGuthaben
            )
            && (int)$_SESSION['Bestellung']->GuthabenNutzen === 1
            && $_SESSION['Bestellung']->fGuthabenGenutzt > 0
            && $_SESSION['Kunde']->fGuthaben > 0;
    }

    /**
     * @param Artikel $product
     * @return bool
     */
    protected function isVoucherProduct(Artikel $product): bool {
        if (defined('FKT_ATTRIBUT_VOUCHER_FLEX') && isset($product->FunktionsAttribute[FKT_ATTRIBUT_VOUCHER_FLEX]) && $product->FunktionsAttribute[FKT_ATTRIBUT_VOUCHER_FLEX]) {
            return true; // Vouchers cannot be bought with subscriptions
        }
        if (defined('FKT_ATTRIBUT_VOUCHER') && isset($product->FunktionsAttribute[FKT_ATTRIBUT_VOUCHER]) && $product->FunktionsAttribute[FKT_ATTRIBUT_VOUCHER]) {
            return true; // Vouchers cannot be bought with subscriptions
        }
        return false;
    }

}
