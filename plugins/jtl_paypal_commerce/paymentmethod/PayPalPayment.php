<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Alert\Alert;
use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Currency;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Link\LinkInterface;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\Payment\Method;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\State;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\LegacyHelper;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\PPCPOrder;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\PPCPOrderInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCaptureDecline;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCapturePending;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\IDToken;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\MerchantIntegrationRequest;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\MerchantIntegrationResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\Amount;
use Plugin\jtl_paypal_commerce\PPC\Order\AmountWithBreakdown;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\InvalidPhoneException;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Patch;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchPurchase;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\AuthResult;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Phone;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Order\Shipping;
use Plugin\jtl_paypal_commerce\PPC\Order\ShippingChangeResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\ClientErrorResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;
use Plugin\jtl_paypal_commerce\PPC\Webhook\EventType;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * Class PayPalPayment
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
abstract class PayPalPayment extends Method implements PayPalPaymentInterface
{
    /** @var PaymentMethod */
    protected PaymentMethod $method;

    /** @var PluginInterface|null */
    protected ?PluginInterface $plugin;

    /** @var array */
    protected array $localizations = [];

    /** @var Configuration|null */
    protected ?Configuration $config = null;

    /** @var PaymentSession */
    protected PaymentSession $sessionCache;

    /** @var Helper */
    protected Helper $helper;

    /** @var Logger */
    protected Logger $logger;

    /** @var PPCPOrderInterface|null */
    protected ?PPCPOrderInterface $ppcpOrder = null;

    /** @var bool */
    protected bool $payAgainProcess = false;

    /** @var string[] */
    protected array $idToken = [];

    /**
     * @inheritDoc
     */
    public function init(int $nAgainCheckout = 0)
    {
        parent::init($nAgainCheckout);

        $this->plugin = PluginHelper::getPluginById('jtl_paypal_commerce');
        if ($this->plugin === null) {
            return $this;
        }

        $this->method       = $this->plugin->getPaymentMethods()->getMethodByID($this->moduleID);
        $this->kZahlungsart = $this->method->getMethodID();
        $this->name         = $this->method->getName();
        $this->sessionCache = PaymentSession::instance($this->moduleID);
        $this->helper       = Helper::getInstance($this->plugin);
        $this->logger       = new Logger(Logger::TYPE_PAYMENT, $this);

        if ($this->plugin->getState() !== State::ACTIVATED) {
            return $this;
        }

        $this->config = PPCHelper::getConfiguration($this->plugin);

        foreach ($this->method->getLocalization() as $localization) {
            $this->localizations[$localization->cISOSprache] = $localization;
        }

        if ($nAgainCheckout > 0) {
            $this->initPayAgain();
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function initPayAgain(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        if (
            $this->config === null
            || $this->plugin === null
            || $this->plugin->getState() !== State::ACTIVATED
            || !parent::isValidIntern($args_arr)
        ) {
            return false;
        }

        if (($args_arr['doOnlineCheck'] ?? false)) {
            return $this->validateMerchantIntegration();
        }

        return true;
    }

    /**
     * @param Order  $order
     * @param string $state
     * @return bool
     */
    protected function isValidOrderState(Order $order, string $state): bool
    {
        $orderState   = $order->getStatus();
        $capture      = $order->getPurchase()->getCapture();
        $captureState = $capture !== null ? $capture->getStatus() : OrderStatus::STATUS_UNKONWN;

        return match ($state) {
            OrderStatus::STATUS_CREATED   => \in_array($orderState, [
                OrderStatus::STATUS_CREATED,
                OrderStatus::STATUS_APPROVED,
                OrderStatus::STATUS_PAYER_ACTION_REQUIRED,
            ], true),
            OrderStatus::STATUS_COMPLETED => $orderState === OrderStatus::STATUS_COMPLETED
                && $captureState === $orderState,
            OrderStatus::STATUS_PENDING => \in_array($orderState, [
                    OrderStatus::STATUS_PENDING,
                    OrderStatus::STATUS_PENDING_APPROVAL,
                ], true) || $captureState === OrderStatus::STATUS_PENDING,
            default => $orderState === $state
                || ($orderState === OrderStatus::STATUS_COMPLETED && $captureState === $state),
        };
    }

    /**
     * @inheritDoc
     */
    public function isAutoCapture(?string $fundingSource = null): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canOrderPayedAgain(Bestellung $order): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return $this->getLocalizedPaymentName($isoCode);
    }

    /**
     * @inheritDoc
     */
    public function getLocalizedPaymentName(?string $isoCode = null): string
    {
        if ($isoCode === null) {
            $isoCode = Shop::getLanguageCode();
        }

        if (!isset($this->localizations[$isoCode])) {
            return $this->getMethod()->getName();
        }

        return $this->localizations[$isoCode]->cName;
    }

    /**
     * @inheritDoc
     */
    public function getSettingPanel(): string
    {
        return Settings::BACKEND_SETTINGS_PANEL_GENERAL;
    }

    /**
     * @inheritDoc
     */
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry
    {
        if (
            !$this->method->getDuringOrder() && !$this->paymentAfterOrderSupported()
            && ($this->isAssigned() || $force)
        ) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($this->method->getName()),
                \__('Zahlung nach Bestellabschluß wird nicht unterstützt.'),
                Shop::getAdminURL() . '/paymentmethods?kZahlungsart=' . $this->method->getMethodID()
                . '&token=' . $_SESSION['jtl_token']
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        if (
            $this->method->getDuringOrder() && !$this->paymentDuringOrderSupported()
            && ($this->isAssigned() || $force)
        ) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($this->method->getName()),
                \__('Zahlung vor Bestellabschluß wird nicht unterstützt.'),
                Shop::getAdminURL() . '/paymentmethods?kZahlungsart=' . $this->method->getMethodID()
                . '&token=' . $_SESSION['jtl_token']
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        return null;
    }

    /**
     * @return bool
     */
    abstract protected function usePurchaseItems(): bool;

    abstract protected function constructPurchase(): PurchaseUnit;

    /**
     * @param object $paymentSession
     * @return bool
     */
    abstract protected function isSessionPayed(object $paymentSession): bool;

    /**
     * @param int $shopOrderId
     * @return string|null
     */
    protected function getCUID(int $shopOrderId = 0): ?string
    {
        if ($shopOrderId > 0) {
            $payment = Shop::Container()->getDB()->getSingleObject(
                'SELECT tbestellstatus.cUID
                    FROM tbestellstatus
                    WHERE tbestellstatus.kBestellung = :orderId
                    ORDER BY tbestellstatus.dDatum DESC',
                ['orderId' => $shopOrderId]
            );
        } else {
            $payment = Shop::Container()->getDB()->getSingleObject(
                'SELECT tbestellstatus.cUID
                    FROM tzahlungsession
                    INNER JOIN tbestellstatus ON tzahlungsession.kBestellung = tbestellstatus.kBestellung
                    WHERE tzahlungsession.cSID = :sessID
                    ORDER BY tzahlungsession.dZeitBezahlt DESC',
                ['sessID' => \session_id()]
            );
        }

        return $payment->cUID ?? null;
    }

    /**
     * @param Configuration $config
     * @return MerchantIntegrationResponse|null
     */
    protected function getMerchantIntegration(Configuration $config): ?MerchantIntegrationResponse
    {
        $client       = new PPCClient(PPCHelper::getEnvironment($this->config));
        $configValues = $config->getConfigValues();
        $workingMode  = $configValues->getWorkingMode();
        $merchantID   = $configValues->getMerchantID($workingMode);
        $partnerID    = \base64_decode(MerchantCredentials::partnerID($workingMode));

        try {
            return new MerchantIntegrationResponse($client->send(new MerchantIntegrationRequest(
                Token::getInstance()->getToken(),
                $partnerID,
                $merchantID
            )));
        } catch (GuzzleException | AuthorizationException | PPCRequestException $e) {
            $this->getLogger()->write(\LOGLEVEL_ERROR, 'MerchantIntegrationRequest: ' . $e->getMessage(), $e);
        }

        return null;
    }

    /**
     * @param AmountWithBreakdown $amount
     * @param int                 $customerGroupId
     * @param Shipping            $shipping
     * @param Cart                $cart
     * @return AmountWithBreakdown
     */
    protected function addSurcharge(
        AmountWithBreakdown $amount,
        int $customerGroupId,
        Shipping $shipping,
        Cart $cart
    ): AmountWithBreakdown {
        $option = $shipping->getOption(true);
        if ($option === null) {
            $amount->setHandling(null);

            return $amount;
        }

        $currencyCode = Frontend::getCurrency()->getCode();
        $countryCode  = $shipping->getAddress()->getCountryCode();
        $surcharge    = LegacyHelper::getPaymentSurchargeDiscount(
            $this->getMethod()->getMethodID(),
            (int)$option->getId(),
            $customerGroupId,
            $countryCode,
            !CustomerGroup::getByID($customerGroupId)->isMerchant()
        );

        foreach (
            LegacyHelper::gibArtikelabhaengigeVersandkostenImWK(
                $countryCode,
                $cart->PositionenArr
            ) as $deptCharge
        ) {
            $surcharge += $deptCharge->fKosten;
        }

        $discount = $amount->getDiscount();
        $amount->setDiscount($surcharge < 0.0
            ? (new Amount())
                ->setValue($discount->getValue() + Currency::convertCurrency(-$surcharge, $currencyCode))
                ->setCurrencyCode($currencyCode)
            : $discount)->setHandling($surcharge > 0.0
            ? (new Amount())
                ->setValue(Currency::convertCurrency($surcharge, $currencyCode))
                ->setCurrencyCode($currencyCode)
            : null);

        return $amount;
    }

    /**
     * @param Customer $customer
     * @param int      $needed
     * @return Payer
     * @throws InvalidPhoneException
     */
    protected function createPayer(Customer $customer, int $needed = Payer::PAYER_DEFAULT): Payer
    {
        $payer = new Payer();
        if (($needed & Payer::PAYER_NAME) > 0) {
            if (!empty($customer->cNachname)) {
                $payer->setSurname(Text::unhtmlentities($customer->cNachname));
            }
            if (!empty($customer->cVorname)) {
                $payer->setGivenName(Text::unhtmlentities($customer->cVorname));
            }
        }
        if (($needed & Payer::PAYER_EMAIL) > 0 && !empty($customer->cMail)) {
            $payer->setEmail($customer->cMail);
        }
        if (($needed & Payer::PAYER_LOCATION) > 0) {
            $payer->setAddress(Address::createFromCustomer($customer));
        }

        if (($needed & Payer::PAYER_BIRTH) > 0) {
            try {
                $payer->setBirthDate(!empty($customer->dGeburtstag) && $customer->dGeburtstag !== '0000-00-00'
                    ? new DateTime($customer->dGeburtstag)
                    : null);
            } catch (Exception) {
                $payer->setBirthDate(null);
            }
        }

        if (($needed & Payer::PAYER_PHONE) > 0) {
            if (!empty($customer->cTel)) {
                $payer->setPhone((new Phone())->setNumber($customer->cTel));
            } elseif (!empty($customer->cMobil)) {
                $payer->setPhone((new Phone())->setNumber($customer->cMobil));
            }
        }

        return $payer;
    }

    /**
     * @param PaymentSourceInterface $paymentSource
     * @param int                    $languageID
     * @param string                 $sppContext
     * @param string                 $payAction
     * @return ExperienceContext
     */
    protected function createExperienceContext(
        PaymentSourceInterface $paymentSource,
        int $languageID,
        string $sppContext = ExperienceContext::SHIPPING_PROVIDED,
        string $payAction = ExperienceContext::USER_ACTION_PAY_NOW
    ): ExperienceContext {
        return ($paymentSource->buildExperienceContext())
            ->setLocale(Helper::getLocaleFromISO(
                Helper::sanitizeISOCode(
                    Shop::Lang()->getIsoFromLangID($languageID === 0
                        ? Shop::Lang()->currentLanguageID
                        : $languageID)->cISO
                )
            ))
            ->setBrandName($this->getShopTitle())
            ->setShippingPreference($sppContext)
            ->setUserAction($payAction);
    }

    /**
     * @param string        $orderHash
     * @param Lieferadresse $shipping
     * @param Cart          $cart
     * @return PurchaseUnit
     */
    protected function createPurchase(
        string $orderHash,
        Lieferadresse $shipping,
        Cart $cart
    ): PurchaseUnit {
        $currency = Frontend::getCurrency();
        $merchant = Frontend::getCustomerGroup()->isMerchant();
        $amount   = AmountWithBreakdown::createFromCart($cart, $currency->getCode(), $merchant);
        $purchase = $this->constructPurchase()
            ->setReferenceId(PurchaseUnit::REFERENCE_DEFAULT)
            ->setAmount($amount)
            ->setCustomId($orderHash)
            ->setDescription($this->helper->getDescriptionFromCart($cart))
            ->setShipping((new Shipping())
                ->setName(
                    \trim(Text::unhtmlentities($shipping->cVorname) . ' ' . Text::unhtmlentities($shipping->cNachname))
                )
                ->setAddress(Address::createFromOrderAddress(
                    LegacyHelper::isAddressEmpty($shipping) ? new Lieferadresse() : $shipping
                )));

        if ($this->usePurchaseItems()) {
            $purchase->addItemsFromCart($cart, $currency, $merchant);
        }

        return $purchase;
    }

    /**
     * @param Shipping $shipping
     * @param Customer $customer
     * @param Cart     $cart
     * @return Shipping
     */
    protected function createShippingOptions(Shipping $shipping, Customer $customer, Cart $cart): Shipping
    {
        $shippingClasses = LegacyHelper::getShippingClasses($cart);
        $shippingMethods = \array_filter(LegacyHelper::getPossibleShippingMethods(
            $customer->cLand ?? '',
            $customer->cPLZ ?? '',
            $customer->getGroupID(),
            $cart
        ), function (stdClass $shippingMethod) use ($shippingClasses, $customer) {
            return $this->isAssigned($shippingClasses, $customer->getGroupID(), $shippingMethod->kVersandart);
        });
        $shippingOption  = $shipping->getOption(true);
        $taxRate         = CustomerGroup::getByID($customer->getGroupID())->isMerchant()
            ? 0
            : (float)Tax::getSalesTax(LegacyHelper::gibVersandkostenSteuerklasse($cart));
        $shippingId      = $shippingOption !== null
            ? $shippingOption->getId()
            : (int)Frontend::get('AktiveVersandart', 0);

        $shipping->clearType()
                 ->setOptions(
                     $shippingMethods,
                     Shop::getLanguageCode(),
                     Frontend::getCurrency()->getCode(),
                     $taxRate
                 )
                 ->selectOption((string)$shippingId);

        return $shipping;
    }

    /**
     * @param Customer $customer
     * @param Cart     $cart
     * @param string   $shippingContext
     * @param string   $payAction
     * @param string   $orderHash
     * @return Order
     * @throws InvalidPhoneException
     */
    protected function constructOrder(
        Customer $customer,
        Cart $cart,
        string $shippingContext,
        string $payAction,
        string $orderHash
    ): Order {
        $fundingSource = PaymentSourceBuilder::isValidPaymentSource($this->getFundingSource())
            ? $this->getFundingSource() : PaymentSourceBuilder::FUNDING_PAYPAL;
        $purchase      = $this->createPurchase($orderHash, Frontend::getDeliveryAddress(), $cart);
        $paymentSource = (new PaymentSourceBuilder($fundingSource))->build();
        $paymentSource->applyPayer($this->createPayer($customer))
                      ->setExperienceContext($this->createExperienceContext(
                          $paymentSource,
                          $customer->getLanguageID(),
                          $shippingContext,
                          $payAction
                      )->setReturnURL($this->getPaymentRetryURL(null))
                       ->setCancelURL($this->getPaymentCancelURL()));
        if ($paymentSource->isPropertyActive('country_code')) {
            $countryCode = (\defined('PPC_DEBUG') && \PPC_DEBUG && PPCHelper::getEnvironment()->isSandbox())
                ? Frontend::getDeliveryAddress()->cLand ?? $customer->cLand
                : $customer->cLand;
            $paymentSource->setCountryCode(Helper::sanitizeISOCode($countryCode));
        }
        $vaultingHelper = new VaultingHelper($this->config);
        if ($this->getCache('ppc_vaulting_enable') === 'Y' && $vaultingHelper->isVaultingEnabled($fundingSource)) {
            $experienceContext = $paymentSource->getExperienceContext() ?? $paymentSource->buildExperienceContext();
            $experienceContext->setReturnURL($this->getPaymentStateURL())
                              ->setCancelURL($this->getPaymentCancelURL());
            $paymentSource->setExperienceContext($experienceContext)
                          ->withVaultRequest();
        }
        $order = (new Order())
            ->addPurchase($purchase)
            ->setPaymentSource($fundingSource, $paymentSource)
            ->setIntent(Order::INTENT_CAPTURE);

        if ($shippingContext === ExperienceContext::SHIPPING_FROM_FILE) {
            $shipping = $this->createShippingOptions($purchase->getShipping() ?? new Shipping(), $customer, $cart);
            /** @var AmountWithBreakdown $amount */
            $amount = $purchase->getAmount();
            $option = $shipping->getOption(true);
            $purchase->setAmount($this->addSurcharge($amount, $customer->getGroupID(), $shipping, $cart)
                                      ->setShipping($option !== null ? $option->getAmount() : null)
                                      ->calculateTotal())
                     ->setShipping($shipping);
        }

        return $order;
    }

    /**
     * @param Order    $ppOrder
     * @param Customer $customer
     * @return Order
     */
    protected function copyOrder(
        Order $ppOrder,
        Customer $customer
    ): Order {
        $copyOrder = (new Order())
            ->addPurchase((new PurchaseUnit($ppOrder->getPurchase()->getData())))
            ->setProcessingInstruction($ppOrder->getProcessingInstruction())
            ->setIntent($ppOrder->getIntent());

        foreach ($ppOrder->getPaymentSources() as $paymentSourceName) {
            $paymentSource = $ppOrder->getPaymentSource($paymentSourceName);
            if ($paymentSource !== null) {
                $paymentSource->applyPayer($this->createPayer($customer));
                $copyOrder->setPaymentSource($paymentSourceName, $paymentSource);
            }
        }

        return $copyOrder;
    }

    /**
     * @param Order  $createOrder
     * @param string $bnCode
     * @return PPCPOrderInterface
     * @throws PPCRequestException
     */
    protected function createPPCPOrder(Order $createOrder, string $bnCode): PPCPOrderInterface
    {
        return ($this->ppcpOrder = PPCPOrder::create($createOrder, $bnCode, $this->getLogger()));
    }

    /**
     * @param string $orderId
     * @return PPCPOrderInterface
     * @throws OrderNotFoundException | PPCRequestException
     */
    protected function loadPPCPOrder(string $orderId): PPCPOrderInterface
    {
        return ($this->ppcpOrder = PPCPOrder::load($orderId, $this->getLogger()));
    }

    /**
     * @param string $fundingSource
     * @return string
     */
    protected function validateFundingSource(string $fundingSource): string
    {
        if ($fundingSource === '') {
            return $this->getFundingSource();
        }
        if ($fundingSource !== $this->getFundingSource()) {
            $this->sessionCache->clearPayment();
            $this->setFundingSource($fundingSource);
        }

        return $fundingSource;
    }

    /**
     * @inheritDoc
     */
    public function validatePaymentConfiguration(PaymentMethod $method, ?array &$settings = null): bool
    {
        if ($settings !== null) {
            return true;
        }

        if (
            $this->paymentDuringOrderSupported()
            && Request::postInt('nWaehrendBestellung', $method->getDuringOrder() ? 1 : 0) === 1
        ) {
            return true;
        }

        if (
            $this->paymentAfterOrderSupported()
            && Request::postInt('nWaehrendBestellung', $method->getDuringOrder() ? 1 : 0) === 0
        ) {
            return true;
        }

        $this->helper->getAlert()->addWarning(
            $this->paymentDuringOrderSupported()
                ? \sprintf(\__('Zahlung nach Bestellabschluß wird von %s nicht unterstützt.'), $method->getName())
                : \sprintf(\__('Zahlung vor Bestellabschluß wird von %s nicht unterstützt.'), $method->getName()),
            'duringCheckoutNotSupported'
        );
        if (isset($_POST['nWaehrendBestellung']) && Request::postInt('einstellungen_bearbeiten') > 0) {
            $_POST['nWaehrendBestellung'] = $this->paymentDuringOrderSupported() ? '1' : '0';
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function validatePayerData(Customer $customer, ?Lieferadresse $shippingAdr = null, ?Cart $cart = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setFundingSource(string $fundingSource): void
    {
        if ($fundingSource !== '') {
            $this->sessionCache->setFundingSource($fundingSource);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFundingSource(): string
    {
        return $this->getDefaultFundingSource();
    }

    /**
     * @inheritDoc
     */
    public function setBNCode(string $bnCode = MerchantCredentials::BNCODE_CHECKOUT): void
    {
        $this->sessionCache->setBNCode($bnCode);
    }

    /**
     * @inheritDoc
     */
    public function getBNCode(string $default = MerchantCredentials::BNCODE_CHECKOUT): string
    {
        return $this->sessionCache->getBNCode($default);
    }

    /**
     * @inheritDoc
     */
    public function renderBackendInformation(JTLSmarty $smarty, PluginInterface $plugin): void
    {
        // nothing do to...
    }

    /**
     * @inheritDoc
     */
    public function isAssigned(string $shippingClasses = '', int $customerGroupID = 0, int $shippingMethodID = 0): bool
    {
        $paymentID = $this->getMethod()->getMethodID();
        $key       = $paymentID . '_' . $shippingClasses . '_' . $customerGroupID . '_' . $shippingMethodID;
        static $assigned;

        if (($assigned[$key] ?? null) === null) {
            $queryShipping      = '';
            $queryCustomerGroup = '';
            $params             = [
                'paymentID' => $paymentID,
            ];
            if ($shippingClasses !== '') {
                $queryShipping = "AND (tversandart.cVersandklassen = '-1'
                    OR tversandart.cVersandklassen LIKE :shippingClass1
                    OR tversandart.cVersandklassen LIKE :shippingClass2
                )";

                $params['shippingClass1'] = '% ' . $shippingClasses . ' %';
                $params['shippingClass2'] = '% ' . $shippingClasses;
            }
            if ($customerGroupID > 0) {
                $queryCustomerGroup = "AND (tversandart.cKundengruppen = '-1'
                    OR tversandart.cKundengruppen LIKE :customerGroup
                )";

                $params['customerGroup'] = '%;' . $customerGroupID . ';%';
            }
            if ($shippingMethodID > 0) {
                $queryShipping        .= ' AND tversandartzahlungsart.kVersandart = :shippingID';
                $params['shippingID'] = $shippingMethodID;
            }

            $result         = Shop::Container()->getDB()->getSingleObject(
                'SELECT COUNT(tversandart.kVersandart) AS cnt
                    FROM tversandartzahlungsart
                    INNER JOIN tversandart
                        ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                    WHERE tversandartzahlungsart.kZahlungsart = :paymentID
                    ' . $queryShipping . '
                    ' . $queryCustomerGroup,
                $params
            );
            $assigned[$key] = $result && (int)$result->cnt > 0;
        }

        return $assigned[$key];
    }

    /**
     * @inheritDoc
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): PaymentMethod
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function storePPOrder(?Order $order): void
    {
        if ($order === null) {
            $this->sessionCache->clear(PaymentSession::ORDERID);
            $this->ppcpOrder = null;

            return;
        }

        $this->sessionCache->setOrderId($order->getId());
        Shop::Container()->getDB()->update('tzahlungsid', 'cId', $this->helper->getSanitizedHash($order), (object)[
            'txn_id' => $order->getId(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getPPOrder(?string $orderId = null): ?Order
    {
        $orderId = $orderId ?? $this->sessionCache->getOrderId();
        if ($this->ppcpOrder === null) {
            if (empty($orderId)) {
                return null;
            }

            try {
                return $this->loadPPCPOrder($orderId)->callGet();
            } catch (PPCRequestException | OrderNotFoundException) {
                return null;
            }
        }

        try {
            return empty($orderId) ? $this->ppcpOrder->callGet() : $this->ppcpOrder->callGet($orderId);
        } catch (PPCRequestException | OrderNotFoundException) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function createPPOrder(
        Customer $customer,
        Cart $cart,
        string $fundingSource,
        string $shippingContext,
        string $payAction,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): ?string {
        $fundingSource = $this->validateFundingSource($fundingSource);
        if ($fundingSource === '') {
            $this->getLogger()->write(\LOGLEVEL_ERROR, 'createPPOrder: funding source is empty');

            return null;
        }

        $ppOrderId = $this->sessionCache->getOrderId();
        $orderHash = $this->generateHash(new Bestellung());
        try {
            $ppOrder = $this->constructOrder($customer, $cart, $shippingContext, $payAction, $orderHash);
        } catch (InvalidPhoneException $e) {
            $this->helper->getAlert()->addError(
                FrontendHandler::getBackendTranslation($e->getMessage()),
                'preparePaymentProcess'
            );

            return null;
        }

        $this->sessionCache->setOrderHash($orderHash);
        if (!empty($ppOrderId)) {
            $ppOrder->setId($ppOrderId);
        }

        $transaction = Transaction::instance();
        $transaction->startTransaction(Transaction::CONTEXT_CREATE);

        try {
            $this->getLogger()->write(\LOGLEVEL_DEBUG, 'createPPOrder:', $ppOrder);
            $ppOrder = $this->createPPCPOrder($ppOrder, $bnCode)->callGet();
            $this->storePPOrder($ppOrder);
            $this->getLogger()->write(\LOGLEVEL_DEBUG, 'createPPOrder: createOrderResponse', $ppOrder);
            $transaction->clearTransaction(Transaction::CONTEXT_CREATE);
            if (!$this->isValidOrderState($ppOrder, OrderStatus::STATUS_CREATED)) {
                $this->getLogger()->write(\LOGLEVEL_NOTICE, 'createPPOrder: UnexpectedOrderState get '
                    . $ppOrder->getStatus() . ' expected ' . OrderStatus::STATUS_CREATED);

                return null;
            }

            return $ppOrder->getId();
        } catch (PPCRequestException $e) {
            $this->unsetCache();
            $this->getLogger()->write(\LOGLEVEL_NOTICE, 'createPPOrder: ' . $e->getName(), $e);
            $this->showErrorResponse($e->getResponse(), new Alert(
                Alert::TYPE_ERROR,
                FrontendHandler::getBackendTranslation($e->getMessage()),
                'createOrderRequest'
            ));
        } catch (OrderNotFoundException) {
            $this->unsetCache();
            $this->getLogger()->write(\LOGLEVEL_NOTICE, 'createPPOrder: created order could not be found');
            $this->helper->getAlert()->addError(
                $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_payment_error'),
                'preparePaymentProcess'
            );
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function verifyPPOrder(string $orderId): Order
    {
        $ppOrder = $this->ppcpOrder === null
            ? $this->loadPPCPOrder($orderId)->callGet()
            : $this->ppcpOrder->callGet($orderId, true);
        $this->storePPOrder($ppOrder);

        return $ppOrder;
    }

    /**
     * @inheritDoc
     */
    public function resetPPOrder(?string $orderId = null): void
    {
        if ($orderId !== null) {
            $ppOrder = $this->getPPOrder($orderId);
            if ($ppOrder !== null && $this->sessionCache->getOrderId() === $ppOrder->getId()) {
                $this->sessionCache->clear(PaymentSession::ORDERID);
                $this->ppcpOrder = null;
            }
        } else {
            $this->unsetCache();
        }
    }

    /**
     * @inheritDoc
     */
    public function recreatePPOrder(
        Order $ppOrder,
        Bestellung $shopOrder,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): Order {
        $clonedOrder = $this->copyOrder($ppOrder, $shopOrder->oKunde);
        $this->sessionCache->setOrderHash($clonedOrder->getCustomId());

        $transaction = Transaction::instance();
        $transaction->startTransaction(Transaction::CONTEXT_CREATE);

        try {
            $this->getLogger()->write(\LOGLEVEL_DEBUG, 'clonePPOrder:', $ppOrder);
            $clonedOrder = $this->createPPCPOrder($clonedOrder, $bnCode)->callGet();
            $this->storePPOrder($clonedOrder);
            $this->getLogger()->write(\LOGLEVEL_DEBUG, 'clonePPOrder: createOrderResponse', $clonedOrder);
            $transaction->clearTransaction(Transaction::CONTEXT_CREATE);
            if (!$this->isValidOrderState($clonedOrder, OrderStatus::STATUS_CREATED)) {
                $this->getLogger()->write(\LOGLEVEL_NOTICE, 'clonePPOrder: UnexpectedOrderState get '
                    . $ppOrder->getStatus() . ' expected ' . OrderStatus::STATUS_CREATED);

                return $ppOrder;
            }

            return $clonedOrder;
        } catch (PPCRequestException $e) {
            $this->unsetCache();
            $this->getLogger()->write(\LOGLEVEL_NOTICE, 'clonePPOrder: ' . $e->getName(), $e);
            $this->showErrorResponse($e->getResponse(), new Alert(
                Alert::TYPE_ERROR,
                FrontendHandler::getBackendTranslation($e->getMessage()),
                'clonePPOrder',
                ['saveInSession' => true]
            ));
        } catch (OrderNotFoundException) {
            $this->unsetCache();
            $this->getLogger()->write(\LOGLEVEL_NOTICE, 'clonePPOrder: created order could not be found');
            Shop::Container()->getAlertService()->addError(
                $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_payment_error'),
                'clonePPOrder',
                ['saveInSession' => true]
            );
        }

        return $ppOrder;
    }

    /**
     * @inheritDoc
     */
    public function get3DSAuthResult(string $authAction): string
    {
        if (\in_array($authAction, [AuthResult::AUTHACTION_CONTINUE, AuthResult::AUTHACTION_REJECT], true)) {
            return $authAction;
        }

        $prefix = Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_';

        return match ($authAction) {
            AuthResult::AUTHACTION_ERROR,
            AuthResult::AUTHACTION_CANCEL => $this->config->getPrefixedConfigItem(
                $prefix . $authAction,
                AuthResult::AUTHACTION_REJECT
            ),
            AuthResult::AUTHACTION_SKIP,
            AuthResult::AUTHACTION_NOTSUPPORTED,
            AuthResult::AUTHACTION_UNABLETOCOMPLETE,
            AuthResult::AUTHACTION_NOTELIGIBLE => $this->config->getPrefixedConfigItem(
                $prefix . $authAction,
                AuthResult::AUTHACTION_CONTINUE
            ),
            default => AuthResult::AUTHACTION_REJECT,
        };
    }

    /**
     * @inheritDoc
     */
    public function getAssignedPayments(Bestellung $shopOrder): array
    {
        $result = [];
        $db     = Shop::Container()->getDB();
        foreach (
            $db->getObjects(
                'SELECT tbestellung.kBestellung, tbestellung.cBestellNr,
                       tzahlungsid.kZahlungsart, tzahlungsid.txn_id
                    FROM tbestellung
                    LEFT JOIN tzahlungseingang ON tzahlungseingang.kBestellung = tbestellung.kBestellung
                    LEFT JOIN tzahlungsid ON tzahlungsid.kBestellung = tbestellung.kBestellung
                    WHERE tbestellung.kBestellung = :orderId
                        AND tzahlungsid.kZahlungsart = :paymentId',
                [
                    'orderId'   => $shopOrder->kBestellung,
                    'paymentId' => $this->getMethod()->getMethodID(),
                ]
            ) as $payment
        ) {
            $orderId = $payment->txn_id;
            if (empty($orderId)) {
                continue;
            }
            try {
                $order = (new TCCaptureDecline())->execute(
                    $this,
                    (new TCCapturePending())->execute(
                        $this,
                        PPCPOrder::load($orderId, $this->getLogger())->callGet()
                    )
                );
            } catch (PPCRequestException | OrderNotFoundException) {
                continue;
            }
            $capture = $order->getPurchase()->getCapture();
            if ($capture === null) {
                continue;
            }
            $result[$orderId] = AssignedPayment::load($capture);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function updatePaymentState(string $paymentHash, Bestellung $order): void
    {
        $db = Shop::Container()->getDB();
        $db->update('tzahlungsession', 'cZahlungsID', $paymentHash, (object)[
            'kBestellung'  => $order->kBestellung,
            'cSID'         => \session_id(),
        ]);
        $db->update('tzahlungsid', 'cId', $paymentHash, (object)[
            'kBestellung' => $order->kBestellung,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentStateURL(?Order $ppOrder = null): ?string
    {
        /** @var LinkInterface $stateLink */
        $stateLink = $this->plugin->getLinks()->getLinks()->first(static function (LinkInterface $link) {
            return $link->getTemplate() === 'pendingpayment.tpl';
        });

        if ($stateLink === null) {
            return null;
        }

        $stateURL = $stateLink->getURL() . '?payment=' . $this->getMethod()->getMethodID();
        if ($ppOrder === null) {
            return $stateURL;
        }

        $shopOrder = $this->helper->getShopOrder($ppOrder);
        $cUID      = $this->getCUID($shopOrder->kBestellung ?? 0);
        if ($cUID !== null) {
            $stateURL .= '&uid=' . $cUID;
        }

        return $stateURL;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentCancelURL(?Order $ppOrder = null): string
    {
        if ($this->isAutoCapture()) {
            $shopOrder = null;
            $ppOrder   = $ppOrder ?? $this->getPPOrder();
            if ($ppOrder !== null) {
                $shopOrder = $this->helper->getShopOrder($ppOrder);
            }
            if ($shopOrder !== null) {
                return $this->getReturnURL($shopOrder);
            }
        }

        return Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php') . '?editZahlungsart=1';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentRetryURL(?Order $ppOrder): string
    {
        return Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php');
    }

    /**
     * @inheritDoc
     */
    public function getValidOrderState(Order $order): string
    {
        $orderState   = $order->getStatus();
        $capture      = $order->getPurchase()->getCapture();
        $captureState = $capture !== null ? $capture->getStatus() : null;

        $state = $orderState === OrderStatus::STATUS_COMPLETED ? $captureState ?? $orderState : $orderState;

        return $state === OrderStatus::STATUS_PENDING_APPROVAL ? OrderStatus::STATUS_PENDING : $state;
    }

    /**
     * @inheritDoc
     */
    public function unsetCache(?string $cKey = null)
    {
        $this->sessionCache->clear($cKey);

        return parent::unsetCache($cKey);
    }

    /**
     * @inheritDoc
     */
    public function generateHash(Bestellung $order): string
    {
        $hash = parent::generateHash($order);
        $hash = \str_starts_with($hash, '_') ? \substr($hash, 1) : $hash;
        $db   = Shop::Container()->getDB();
        $db->delete('tzahlungsid', 'cId', $hash);
        $db->insert('tzahlungsid', (object)[
            'kBestellung'  => $order->kBestellung ?? 0,
            'kZahlungsart' => $this->getMethod()->getMethodID(),
            'cId'          => $hash,
            'txn_id'       => '',
            'dDatum'       => 'NOW()',
        ]);

        return $hash;
    }

    /**
     * @inheritDoc
     */
    public function redirectOnPaymentSuccess(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getReturnURL(Bestellung $order): string
    {
        if (
            $this->payAgainProcess === true
            || Shop::getSettings([\CONF_KAUFABWICKLUNG])['kaufabwicklung']['bestellabschluss_abschlussseite'] === 'A'
        ) {
            $orderHash = $this->getOrderHash($order);
            if ($orderHash !== null) {
                return Shop::getURL() . '/bestellabschluss.php?i=' . $orderHash;
            }
        }

        if (empty($order->BestellstatusURL)) {
            $cUID = $this->getCUID();

            return $cUID !== null
                ? Shop::Container()->getLinkService()->getStaticRoute('status.php') . '?uid=' . $cUID
                : Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?bestellungen=1';
        }

        return $order->BestellstatusURL;
    }

    /**
     * @inheritDoc
     */
    public function addIncomingPayment(Bestellung $order, object $payment)
    {
        $payData = $payment;

        $order->cZahlungsartName = $this->getMethod()->getName();
        if ($payment instanceof Order) {
            $capture = $payment->getPurchase()->getCapture() ?? new Capture();
            $amount  = $capture->getAmount();
            $fee     = $amount->getBreakdownItem('paypal_fee');
            $payer   = $payment->getPayer();
            $payData = (object)[
                'fBetrag'           => $amount->getValue(),
                'fZahlungsgebuehr'  => $fee !== null ? $fee->getValue() : 0,
                'cISO'              => $amount->getCurrencyCode(),
                'cZahler'           => $payer !== null ? $payer->getSurname() . ', ' . $payer->getEmail() : '',
                'cHinweis'          => $capture->getId(),
            ];
        }

        return parent::addIncomingPayment($order, $payData);
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatusToPaid(Bestellung $order)
    {
        $paySum = Shop::Container()->getDB()->getSingleObject(
            'SELECT SUM(fBetrag) AS incommingSum
                FROM tzahlungseingang
                WHERE kBestellung = :orderId',
            ['orderId' => $order->kBestellung]
        );
        if ($paySum !== null && (float)$order->fGesamtsumme > (float)$paySum->incommingSum) {
            return $this;
        }

        Shop::Container()->getDB()->update(
            'tbestellung',
            'kBestellung',
            $order->kBestellung,
            (object)[
                'cStatus'          => \BESTELLUNG_STATUS_BEZAHLT,
                'dBezahltDatum'    => 'NOW()',
                'cZahlungsartName' => $this->getMethod()->getName(),
            ]
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finalizeOrder(Bestellung $order, string $hash, array $args): bool
    {
        $ppOrder = $this->getPPOrder();
        if ($ppOrder === null) {
            $ppOrder = new Order();
            $ppOrder->addPurchase((new PurchaseUnit())
                    ->setInvoiceId($order->cBestellNr));
        }
        $invoiceId = $ppOrder->getPurchase()->getInvoiceId();
        if (empty($invoiceId)) {
            $invoiceId = $args['invoiceId'] ?? null;
        }
        if ($order->cBestellNr !== $invoiceId && !empty($invoiceId)) {
            $order->cBestellNr = $invoiceId;
        }

        if (!$this->helper->existsOrder($order)) {
            $this->helper->addFundingSourceToCart($this->getFundingSource());

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function finalizeOrderInDB(Bestellung $order): void
    {
        $ppOrder = $this->getPPOrder();
        if ($ppOrder !== null) {
            $order->cBestellNr = $ppOrder->getInvoiceId();
        }
        // prevent wawi synchronisation on a pending order
        $order->cAbgeholt = 'P';
    }

    /**
     * @inheritDoc
     */
    public function sendMail(int $orderID, string $type, $additional = null)
    {
        if (\str_starts_with($type, 'kPlugin_' . $this->plugin->getID())) {
            $order = new Bestellung($orderID);
            $order->fuelleBestellung(false);
            $data = (object)[
                'tkunde'      => new Customer($order->kKunde),
                'tbestellung' => $order,
            ];
            try {
                $mailer = Shop::Container()->get(Mailer::class);
                $mail   = new Mail();
                $mailer->send($mail->createFromTemplateID($type, $data));
            } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
                // no mailer found, mail can't be sent - ignore that
            }

            return $this;
        }

        return parent::sendMail($orderID, $type, $additional);
    }

    /**
     * @param string|null $msg
     * @param string|null $exitUrl
     * @return void
     */
    protected function raisePaymentError(?string $msg = null, ?string $exitUrl = null): void
    {
        $localMsg = $this->plugin->getLocalization()
                                 ->getTranslation($msg ?? 'jtl_paypal_commerce_payment_error') ?? $msg;

        if ($localMsg !== null) {
            $this->helper->getAlert()->addError($localMsg, 'paymentError');
        }

        Helper::redirectAndExit($exitUrl ?? $this->getPaymentCancelURL());
    }

    /**
     * @param ClientErrorResponse $response
     * @param Alert|null          $default
     * @return void
     */
    protected function showErrorResponse(ClientErrorResponse $response, ?Alert $default = null): void
    {
        $errDetail = $response->getDetail();
        $as        = $this->helper->getAlert();
        if ($errDetail !== null) {
            $this->getLogger()->write(\LOGLEVEL_NOTICE, $errDetail->getDescription(), $response);
            $issue = $errDetail->getIssue() ?? '';
            $msg   = FrontendHandler::getBackendTranslation($issue);
            if ($msg === $issue) {
                $msg = FrontendHandler::getBackendTranslation($errDetail->getDescription() ?? '');
            }
            if ($msg !== '') {
                $as->addError($msg, 'createOrderRequest');

                return;
            }
        }

        if ($default !== null) {
            $as->removeAlertByKey($default->getKey());
            $as->getAlertlist()->push($default);
        }
    }

    /**
     * @inheritdoc
     */
    public function handleOrder(Order $order, ?Bestellung $shopOrder = null, bool $return = false): void
    {
        $orderExists  = $shopOrder !== null && $this->helper->existsOrder($shopOrder);
        $localization = $this->plugin->getLocalization();
        if ($this->isValidOrderState($order, OrderStatus::STATUS_DECLINED)) {
            if (!$orderExists) {
                // payment is declined, no order was created => create shop order and goto success page
                $this->helper->persistOrder($shopOrder, $order, $this, []);
            }
            $this->helper->declinePayment($shopOrder, $order->getPurchase()->getCapture(), $this);
            $this->onPaymentComplete($order);
            $order->setLink((object)[
                'rel'  => 'paymentRedirect',
                'href' => $this->getReturnURL($shopOrder),
            ])->setCustomProcessMessage(\sprintf(
                $localization->getTranslation('jtl_paypal_commerce_payment_declined'),
                $order->getInvoiceId(),
                $this->getLocalizedPaymentName()
            ));
        } elseif ($this->isValidOrderState($order, OrderStatus::STATUS_PENDING)) {
            if (!$orderExists) {
                // payment is pending but shop order failed => create shop order and goto notification page
                $this->helper->persistOrder($shopOrder, $order, $this, []);
            }
            $capture = $order->getPurchase()->getCapture();
            if ($capture !== null) {
                $this->helper->capturePayment($shopOrder, $capture, $order, $this);
            }
            $order->setLink((object)[
                'rel'  => 'paymentRedirect',
                'href' => $this->getPaymentStateURL($order),
            ]);
        } elseif ($this->isValidOrderState($order, OrderStatus::STATUS_COMPLETED)) {
            if (!$orderExists) {
                // payment is created but shop order failed => create shop order and goto success page
                $this->helper->persistOrder($shopOrder, $order, $this, []);
            }
            $this->helper->capturePayment($shopOrder, $order->getPurchase()->getCapture(), $order, $this);
            $this->onPaymentComplete($order);
            $order->setLink((object)[
                'rel'  => 'paymentRedirect',
                'href' => $this->getReturnURL($shopOrder),
            ]);
        } elseif ($this->isValidOrderState($order, OrderStatus::STATUS_APPROVED)) {
            if ($orderExists) {
                // shop order exist, possible case are paypal server error during capture or approval => goto order page
                $order->setLink((object)[
                    'rel'  => 'paymentRedirect',
                    'href' => $this->getReturnURL($shopOrder),
                ]);
            } else {
                // payment ist created, shop order does not exists => goto order completion page
                $order->setLink((object)[
                    'rel'  => 'paymentRedirect',
                    'href' => $this->getPaymentRetryURL($order),
                ]);
            }
        } elseif (
            $this->isValidOrderState($order, OrderStatus::STATUS_CREATED)
            || $this->isValidOrderState($order, OrderStatus::STATUS_PAYER_ACTION_REQUIRED)
        ) {
            if ($orderExists) {
                // shop order exist, possible case are paypal server error during capture or approval => goto order page
                $order->setLink((object)[
                    'rel'  => 'paymentRedirect',
                    'href' => $this->getReturnURL($shopOrder),
                ]);
            } else {
                // payment ist created, shop order does not exists => goto payment page
                if ($this->isValidOrderState($order, OrderStatus::STATUS_PAYER_ACTION_REQUIRED)) {
                    (new VaultingHelper($this->config, $shopOrder, $this->getDB()))->disableVaultingTemporary($this);
                    $order->setLink((object)[
                        'rel'  => 'paymentRedirect',
                        'href' => $this->getPaymentRetryURL($order),
                    ]);
                    $this->helper->getAlert()->addError(
                        $localization->getTranslation('jtl_paypal_commerce_payer_action_required_general'),
                        'handlePayerActionRequired',
                        ['linkText' => $localization->getTranslation('jtl_paypal_commerce_payer_action_required')]
                    );
                } else {
                    $order->setLink((object)[
                        'rel'  => 'paymentRedirect',
                        'href' => $this->getPaymentCancelURL($order),
                    ]);
                }
            }
        }

        $this->getLogger()->write(\LOGLEVEL_DEBUG, 'handleOrder', (object)[
            'order'     => $order->getId() . ': ' . $this->getValidOrderState($order),
            'shopOrder' => $orderExists ? $shopOrder->cBestellNr : '',
        ]);

        $processMsg = $order->getCustomProcessMessage();
        if (!$return && $processMsg !== '') {
            $this->helper->getAlert()->addInfo($processMsg, 'paymentState');
        }
        $redirect = $order->getLink('paymentRedirect');
        if (!$return && $redirect !== null) {
            Helper::redirectAndExit($redirect);
        }
    }

    /**
     * @inheritDoc
     */
    public function onPendingCapture(): void
    {
        $ppOrder = (new TCCaptureDecline())->execute(
            $this,
            (new TCCapturePending())->execute($this, $this->getPPOrder(), Frontend::getCustomer(), Frontend::getCart()),
            Frontend::getCustomer(),
            Frontend::getCart()
        );
        if ($ppOrder === null) {
            throw new OrderNotFoundException();
        }

        $this->handleOrder($ppOrder, $this->helper->getShopOrder($ppOrder), true);
    }

    /**
     * @inheritDoc
     */
    public function onPaymentState(PaymentStateResult $result, bool $timeOut = false): void
    {
        $ppOrder   = $this->getPPOrder();
        $shopOrder = $ppOrder !== null ? $this->helper->getShopOrder($ppOrder) : null;
        if ($shopOrder !== null && ($result->getState() !== OrderStatus::STATUS_PENDING || $timeOut)) {
            $result->setRedirect($this->getReturnURL($shopOrder));
        }
        // Workaround for session cleanup in case of multiple reloads during capture
        if (
            $shopOrder !== null
            && $result->getState() === OrderStatus::STATUS_COMPLETED
            && (Frontend::get('ppPersistOrder') ?? 0) === $shopOrder->kBestellung
        ) {
            try {
                Frontend::getInstance(false)->cleanUp();
                Frontend::set('ppPersistOrder', null);
            } catch (Exception $e) {
                $this->getLogger()->write(\LOGLEVEL_ERROR, 'persistOrder: session error (' . $e->getMessage() . ')');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function onPaymentComplete(Order $order): void
    {
        $shopOrder = $this->helper->getShopOrder($order);
        if ($shopOrder === null) {
            return;
        }

        $db = $this->getDB();
        $db->update(
            'tbestellung',
            ['kBestellung', 'cAbgeholt'],
            [$shopOrder->kBestellung, 'P'],
            (object)[
                'cAbgeholt' => 'N',
            ]
        );
        $db->update(
            'tzahlungseingang',
            ['kBestellung', 'cZahlungsanbieter', 'cAbgeholt'],
            [$shopOrder->kBestellung, $this->getMethod()->getName(), 'P'],
            (object)[
                'cAbgeholt' => 'N',
            ]
        );

        $source        = $order->getPaymentSources()[0] ?? '';
        $paymentSource = $order->getPaymentSource($source);
        $vaulting      = new VaultingHelper($this->config, $shopOrder, $db);
        if ($paymentSource !== null) {
            $vault = $paymentSource->getAttribute('vault');
            if ($vault !== null) {
                $vaulting->storeVault($source, $paymentSource->getAttribute('vault'));
            }
        }

        $this->unsetCache();
    }

    /**
     * @inheritDoc
     */
    public function handleCaptureWebhook(string $eventType, Capture $capture, object $payment): bool
    {
        $this->getLogger()->write(\LOGLEVEL_DEBUG, 'handleCaptureWebhook', $payment);

        $ppOrder   = !empty($payment->txn_id) ? $this->getPPOrder($payment->txn_id) : null;
        $shopOrder = $ppOrder !== null ? $this->helper->getShopOrder($ppOrder) : null;

        if ($ppOrder === null || $shopOrder === null) {
            $this->getLogger()->write(\LOGLEVEL_ERROR, 'handleCaptureWebhook: shop order does not exists', $payment);

            return false;
        }

        $ppOrder = (new TCCaptureDecline())->execute($this, (new TCCapturePending(false))->execute($this, $ppOrder));
        switch ($eventType) {
            case EventType::CAPTURE_COMPLETED:
                if ($this->isValidOrderState($ppOrder, OrderStatus::STATUS_COMPLETED)) {
                    $this->helper->capturePayment($shopOrder, $capture, $ppOrder, $this);
                } elseif ($this->isValidOrderState($ppOrder, OrderStatus::STATUS_DECLINED)) {
                    $this->helper->declinePayment($shopOrder, $capture, $this);
                }
                $this->onPaymentComplete($ppOrder);

                return true;
            case EventType::CAPTURE_DENIED:
            case EventType::CAPTURE_REVERSED:
                $this->helper->declinePayment($shopOrder, $capture, $this);
                $this->onPaymentComplete($ppOrder);

                return true;
            default:
                $this->getLogger()->write(\LOGLEVEL_DEBUG, 'handleCaptureWebhook - event type '
                    . $eventType . ' not supported');
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleShippingData(Order $order, ShippingChangeResponse $shippingData): ?Patch
    {
        $cart            = Frontend::getCart();
        $shippingClasses = LegacyHelper::getShippingClasses($cart);
        $shippingAddress = $shippingData->getShippingAddress() ?? new Address();
        $shippingOption  = $shippingData->getShippingOption();
        $countryCode     = $shippingAddress->getCountryCode();
        $customerGroupId = Frontend::getCustomerGroup()->getID();
        $frontendCountry = Frontend::get('cLieferlandISO');
        $shippingId      = $shippingOption !== null
            ? $shippingOption->getId()
            : (int)Frontend::get('AktiveVersandart', 0);
        $shippingMethods = \array_filter(LegacyHelper::getPossibleShippingMethods(
            $countryCode,
            $shippingAddress->getPostalCode(),
            $customerGroupId,
            $cart
        ), function (stdClass $shippingMethod) use ($shippingClasses, $customerGroupId) {
            return $this->isAssigned($shippingClasses, $customerGroupId, $shippingMethod->kVersandart);
        });

        if ($frontendCountry !== $countryCode) {
            Tax::setTaxRates($countryCode, true);
        }
        $taxRate  = CustomerGroup::getByID($customerGroupId)->isMerchant()
            ? 0
            : (float)Tax::getSalesTax(LegacyHelper::gibVersandkostenSteuerklasse($cart));
        $purchase = $order->getPurchase();
        $shipping = $purchase->getShipping() ?? new Shipping();
        $option   = $shipping->clearType()
                           ->setOptions(
                               $shippingMethods,
                               Shop::getLanguageCode(),
                               Frontend::getCurrency()->getCode(),
                               $taxRate
                           )
                           ->selectOption((string)$shippingId);
        $amount   = $this->addSurcharge($purchase->getAmount(), $customerGroupId, $shipping, $cart);
        $purchase->setAmount(
            $amount->setShipping($option !== null ? $option->getAmount() : null)->calculateTotal()
        )->setShipping($shipping);
        try {
            $order = $this->ppcpOrder->callPatch(new Order($order->getData()));
        } catch (PPCRequestException | OrderNotFoundException $e) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'handleShippingData: OrderPatchFailed - ' . $e->getMessage()
            );
        }
        if ($frontendCountry !== $countryCode) {
            Tax::setTaxRates($frontendCountry, true);
        }

        return $option !== null ? new PatchPurchase($order->getPurchase()) : null;
    }

    public function prepareVaultedPayment(string $fundingSource, Customer $customer, string $shippingHash): string
    {
        $vaultHelper = new VaultingHelper($this->config, null, $this->getDB());
        if (!$vaultHelper->isVaultingEnabled($fundingSource, $customer->getID())) {
            return '';
        }

        $validCustomerVault = $vaultHelper->getValidCustomerVault($customer->getID(), $this, $shippingHash);
        if (empty($validCustomerVault)) {
            return '';
        }

        if (empty($this->idToken[$validCustomerVault])) {
            try {
                $idToken = IDToken::getInstance(
                    PPCHelper::getEnvironment($this->config),
                    $validCustomerVault,
                    $this->getLogger()
                );
            } catch (AuthorizationException) {
                return '';
            }
            $this->idToken[$validCustomerVault] = $idToken->getIDToken();
        }

        return $this->idToken[$validCustomerVault];
    }
}
