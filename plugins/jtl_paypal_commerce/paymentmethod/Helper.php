<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Checkout\Bestellung;
use JTL\Checkout\OrderHandler;
use JTL\Events\Dispatcher;
use JTL\Helpers\Text;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Plugin\HookManager;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\SimpleMail;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\frontend\ControllerFactory;
use Plugin\jtl_paypal_commerce\PPC\APM;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use stdClass;

use function Functional\first;

/**
 * Class Factory
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
final class Helper
{
    /** @var static[] */
    private static array $instance = [];

    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var AlertServiceInterface|null */
    private ?AlertServiceInterface $alertService = null;

    /**
     * Factory constructor.
     * @param PluginInterface $plugin
     */
    protected function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;

        self::$instance[$plugin->getPluginID()] = $this;
    }

    /**
     * @param PluginInterface $plugin
     * @return self
     */
    public static function getInstance(PluginInterface $plugin): self
    {
        return self::$instance[$plugin->getPluginID()] ?? new self($plugin);
    }

    /**
     * @return AlertServiceInterface
     */
    public function getAlert(): AlertServiceInterface
    {
        if ($this->alertService === null) {
            $this->alertService = AlertService::getInstance();
        }

        return $this->alertService;
    }

    /**
     * @param int $methodID
     * @return PaymentMethod|null
     */
    public function getMethodFromID(int $methodID): ?PaymentMethod
    {
        return first(
            $this->plugin->getPaymentMethods()->getMethods(),
            static function (PaymentMethod $item) use ($methodID) {
                return $item->getMethodID() === $methodID;
            }
        );
    }

    /**
     * @param int  $methodID
     * @param bool $payAgainProcess
     * @return PayPalPaymentInterface|null
     * @uses PayPalCommerce
     * @uses PayPalPUI
     * @uses PayPalACDC
     * @uses PayPalGPay
     * @uses PayPalApplePay
     */
    public function getPaymentFromID(int $methodID, bool $payAgainProcess = false): ?PayPalPaymentInterface
    {
        if (($method = $this->getMethodFromID($methodID)) === null) {
            return null;
        }

        $classname = $method->getClassName();
        if (\class_exists($classname)) {
            $paymentMethod = new $classname($method->getModuleID(), $payAgainProcess ? 1 : 0);
            if (\is_a($paymentMethod, PayPalPaymentInterface::class)) {
                return $paymentMethod;
            }
        }

        return null;
    }

    /**
     * @param string $className
     * @return PayPalPaymentInterface|null
     * @uses PayPalCommerce
     * @uses PayPalPUI
     */
    public function getPaymentFromName(string $className): ?PayPalPaymentInterface
    {
        $class = first(
            $this->plugin->getPaymentMethods()->getClasses(),
            static function (stdClass $item) use ($className) {
                return $item->cClassName === $className;
            }
        );

        if (
            $class !== null
            && ($method = $this->plugin->getPaymentMethods()->getMethodByID($class->cModulId)) !== null
        ) {
            return $this->getPaymentFromID($method->getMethodID());
        }

        return null;
    }

    /**
     * @param string      $langCode
     * @param string|null $fundingSource
     * @return Collection
     */
    public function getFundingMethodsMapping(string $langCode, ?string $fundingSource = null): Collection
    {
        $langCode = \strtoupper($langCode);
        $config   = PPCHelper::getConfiguration($this->plugin);
        $langVars = $this->plugin->getLocalization();
        $apm      = new APM($config);

        return (new Collection($fundingSource !== null ? [$fundingSource] : $apm->getEnabled(false)))
            ->mapWithKeys(static function (string $key) use ($langVars, $langCode, $config, $apm) {
                $item = (object)[
                    'title'   => $langVars->getTranslation('jtl_paypal_commerce_fundingmethod_' . $key, $langCode),
                    'picture' => $config->getPrefixedConfigItem($key . '_APM_pictureURL', ''),
                    'note'    => $config->getPrefixedConfigItem($key . '_APM_desc_' . $langCode, ''),
                    'fields'  => $apm->getPaymentFields($key),
                    'sort'    => $config->getPrefixedConfigItem($key . '_APM_sortNr', '0'),
                ];

                return [$key => $item] ?? [$key => null];
            });
    }

    /**
     * @param Cart $cart
     * @param int  $maxLen
     * @return string
     */
    public function getDescriptionFromCart(Cart $cart, int $maxLen = 127): string
    {
        $placeHolder = $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_purchase_placeholder');
        $dropLen     = $maxLen - (int)\mb_strlen($placeHolder);
        $description = '';
        $itemsNamed  = 0;

        foreach ($cart->PositionenArr as $cartItem) {
            if ($cartItem->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL) {
                $itemsNamed++;
                $itemName    = \is_array($cartItem->cName)
                    ? $cartItem->cName[$this->plugin->getLocalization()->getCurrentLanguageCode()]
                    : $cartItem->cName;
                $description .= ($description === '' ? '' : ', ') . $itemName;
                if (\mb_strlen($description) > $dropLen) {
                    $itemsTotal  = $cart->gibAnzahlPositionenExt([\C_WARENKORBPOS_TYP_ARTIKEL]);
                    $moreDesc    = '... ' . \sprintf($placeHolder, $itemsTotal - $itemsNamed);
                    $description = \mb_substr($description, 0, $maxLen - \mb_strlen($moreDesc) - 1);
                    $description .= $moreDesc;

                    break;
                }
            }
        }

        return $description;
    }

    /**
     * @param string $orderNumber
     * @param string $shopTitle
     * @return string
     */
    public function getSimpleDescription(string $orderNumber, string $shopTitle): string
    {
        $description  = $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_purchase_description');
        $placeHolders = [
            '{{OrderNumber}}' => $orderNumber,
            '{{ShopName}}'    => $shopTitle,
            '{{OrderDate}}'   => (new DateTime())->format('Y-m-d'),
        ];
        foreach ($placeHolders as $placeHolder => $value) {
            $description = str_replace($placeHolder, $value, $description);
        }

        return $description;
    }

    /**

    /**
     * @return Dispatcher
     */
    public function revokeHookDispatcher(): Dispatcher
    {
        $hookManager = HookManager::getInstance();
        $dispatcher  = $hookManager->getDispatcher();
        $hookManager->setDispatcher(new Dispatcher());

        return $dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     * @return void
     */
    public function restoreHookDispatcher(Dispatcher $dispatcher): void
    {
        HookManager::getInstance()->setDispatcher($dispatcher);
    }

    /**
     * @param string $fundingSource
     * @return void
     */
    public function addFundingSourceToCart(string $fundingSource): void
    {
        if ($fundingSource === '') {
            return;
        }

        $cart            = Frontend::getCart();
        $orderAttributes = $cart->OrderAttributes ?? [];
        $attribute       = first($orderAttributes, function (stdClass $item) {
            return $item->cName === 'PAYPAL_FUNDING_SOURCE';
        });
        if ($attribute === null) {
            $orderAttributes[] = (object)[
                'cName'  => 'PAYPAL_FUNDING_SOURCE',
                'cValue' => $fundingSource,
            ];
        } else {
            $attribute->cValue = $fundingSource;
        }

        $cart->OrderAttributes = $orderAttributes;
    }

    public function isCartCompleteAndValid(Order $order): bool
    {
        $cart       = Frontend::getCart();
        $wkChecksum = Cart::getChecksum($cart);
        $linkHelper = Shop::Container()->getLinkService();
        $customer   = Frontend::getCustomer();
        $controller = ControllerFactory::getOrderCompleteController();
        $linkObject = (object)[
            'rel'  => 'redirect',
            'href' => $linkHelper->getStaticRoute('warenkorb.php'),
        ];

        $cart->pruefeLagerbestaende()
             ->loescheDeaktiviertePositionen();
        if ($cart->checkIfCouponIsStillValid() === false) {
            Frontend::set('checkCouponResult.ungueltig', 3);
            $linkObject->href = $linkHelper->getStaticRoute('warenkorb.php');
        } elseif (!empty($cart->cChecksumme) && $wkChecksum !== $cart->cChecksumme) {
            if (!$cart->posTypEnthalten(\C_WARENKORBPOS_TYP_ARTIKEL)) {
                CartHelper::deleteAllSpecialItems();
            }
            $this->getAlert()->addWarning(
                Shop::Lang()->get('yourbasketismutating', 'checkout'),
                'warningCartYourbasketismutating',
                ['saveInSession' => true]
            );
            $linkObject->href = $linkHelper->getStaticRoute('warenkorb.php');
        } elseif (!$controller->isOrderComplete($cart)) {
            $linkObject->href = $linkHelper->getStaticRoute('bestellvorgang.php')
                . '?fillOut=' . $controller->getErorCode();
        } elseif (isset($customer->cMail) === true && SimpleMail::checkBlacklist($customer->cMail)) {
            $linkObject->href = $linkHelper->getStaticRoute('bestellvorgang.php') . '?mailBlocked=1';
        } elseif ($cart->removeParentItems() > 0) {
            $this->getAlert()->addWarning(
                Shop::Lang()->get('warningCartContainedParentItems', 'checkout'),
                'warningCartContainedParentItems',
                ['saveInSession' => true]
            );
        } else {
            return true;
        }

        $order->setLink($linkObject);

        return false;
    }

    /**
     * @param Bestellung $shopOrder
     * @return bool
     */
    public function existsOrder(Bestellung $shopOrder): bool
    {
        if (!empty($shopOrder->kBestellung)) {
            $exists = (int)$shopOrder->kBestellung;
        } else {
            $exists = Shop::Container()->getDB()->getSingleInt(
                'SELECT kBestellung FROM tbestellung WHERE cBestellNr = :invoiceId',
                'kBestellung',
                [
                    'invoiceId' => $shopOrder->cBestellNr,
                ]
            );
        }

        if ($exists > 0) {
            $shopOrder->loadFromDB($exists)->fuelleBestellung();

            return true;
        }

        return false;
    }

    /**
     * @param Order $order
     * @return Bestellung|null
     */
    public function getShopOrder(Order $order): ?Bestellung
    {
        $shopOrder             = new Bestellung();
        $shopOrder->cBestellNr = $order->getInvoiceId();

        return $this->existsOrder($shopOrder) ? $shopOrder : null;
    }

    /**
     * @param Bestellung $shopOrder
     * @param string     $attribute
     * @return string|null
     */
    public function getShopOrderAttribute(Bestellung $shopOrder, string $attribute): ?string
    {
        foreach (($shopOrder->OrderAttributes ?? []) as $item) {
            if (($item->cName ?? '') === $attribute) {
                return $item->cValue ?? null;
            }
        }

        return null;
    }

    /**
     * @param PayPalPaymentInterface $ppMethod
     * @param Bestellung             $shopOrder
     * @return Order|null
     */
    public function getPPOrder(PayPalPaymentInterface $ppMethod, Bestellung $shopOrder): ?Order
    {
        $payment = Shop::Container()->getDB()->getSingleObject(
            "SELECT txn_id FROM tzahlungsid
                WHERE kBestellung = :orderId
                    AND kZahlungsart = :paymentId
                    AND txn_id != ''
                ORDER BY dDatum DESC",
            [
                'orderId'   => $shopOrder->kBestellung,
                'paymentId' => $shopOrder->kZahlungsart,
            ]
        );

        return $payment !== null ? $ppMethod->getPPOrder($payment->txn_id) : null;
    }

    /**
     * @param string $orderNumber
     * @param string $paymentId
     * @return object|null
     */
    public function getIncommingPayment(string $orderNumber, string $paymentId): ?stdClass
    {
        return Shop::Container()->getDB()->getSingleObject(
            'SELECT tbestellung.kBestellung, tzahlungseingang.kZahlungseingang,
                tzahlungseingang.fBetrag, tzahlungseingang.fZahlungsgebuehr,
                tzahlungseingang.cISO, tzahlungseingang.cAbgeholt
                FROM tbestellung
                LEFT JOIN tzahlungseingang
                    ON tzahlungseingang.kBestellung = tbestellung.kBestellung AND tzahlungseingang.cHinweis = :paymentId
                WHERE tbestellung.cBestellNr = :orderNumber',
            [
                'paymentId'   => $paymentId,
                'orderNumber' => $orderNumber,
            ]
        );
    }

    /**
     * @param int $incommingPaymentId
     * @return void
     */
    public function dropIncommingPayment(int $incommingPaymentId): void
    {
        Shop::Container()->getDB()->delete('tzahlungseingang', 'kZahlungseingang', $incommingPaymentId);
    }

    /**
     * @param Bestellung             $shopOrder
     * @param Order                  $ppOrder
     * @param PayPalPaymentInterface $ppMethod
     * @param array                  $args
     * @return void
     */
    public function persistOrder(
        Bestellung $shopOrder,
        Order $ppOrder,
        PayPalPaymentInterface $ppMethod,
        array $args
    ): void {
        $ppHash = $this->getSanitizedHash($ppOrder);
        if (!$ppMethod->finalizeOrder($shopOrder, $ppHash, $args)) {
            if ($ppMethod->redirectOnCancel()) {
                self::redirectAndExit($ppMethod->getPaymentCancelURL($ppOrder));
            }

            return;
        }

        $logger = $ppMethod->getLogger();
        $logger->write(\LOGLEVEL_DEBUG, 'persistOrder', (object)[
            'shopOrder' => $shopOrder,
            'ppOrder'   => $ppOrder,
        ]);

        $db           = Shop::Container()->getDB();
        $orderHandler = new OrderHandler($db, Frontend::getCustomer(), Frontend::getCart());

        $shopOrder->kBestellung = $orderHandler->finalizeOrder($ppOrder->getInvoiceId())->kBestellung;
        $shopOrder->fuelleBestellung(false, 1);
        Frontend::set('ppPersistOrder', $shopOrder->kBestellung);
        $orderHandler->saveUploads($shopOrder);
        try {
            Frontend::getInstance(false)->cleanUp();
            Frontend::set('ppPersistOrder', null);
        } catch (Exception $e) {
            $logger->write(\LOGLEVEL_ERROR, 'persistOrder: session error (' . $e->getMessage() . ')');
        }
        $ppMethod->handleNotification($shopOrder, $ppHash, $args);
        $ppMethod->updatePaymentState($ppHash, $shopOrder);
    }

    /**
     * @param Bestellung             $shopOrder
     * @param Capture                $capture
     * @param Order                  $order
     * @param PayPalPaymentInterface $ppMethod
     * @return void
     */
    public function capturePayment(
        Bestellung $shopOrder,
        Capture $capture,
        Order $order,
        PayPalPaymentInterface $ppMethod
    ): void {
        if ($capture->getStatus() !== OrderStatus::STATUS_COMPLETED) {
            // actually we can not handle uncomplete payments
            return;
        }

        $incommingPayment = $this->getIncommingPayment($shopOrder->cBestellNr, $capture->getId());
        $amount           = $capture->getAmount();
        if (
            $incommingPayment === null || (
                $amount->getValue() === (float)$incommingPayment->fBetrag
                && $amount->getCurrencyCode() === $incommingPayment->cISO
            )
        ) {
            return;
        }

        $logger   = $ppMethod->getLogger();
        $sendMail = false;
        $fee      = $amount->getBreakdownItem('paypal_fee');
        $payer    = $order->getPayer();
        $payData  = (object)[
            'fBetrag'           => $amount->getValue(),
            'fZahlungsgebuehr'  => $fee !== null ? $fee->getValue() : 0,
            'cISO'              => $amount->getCurrencyCode(),
            'cZahler'           => $payer !== null ? $payer->getSurname() . ', ' . $payer->getEmail() : '',
            'cHinweis'          => $capture->getId(),
            'cAbgeholt'         => 'P',
        ];
        if (empty($incommingPayment->kZahlungseingang)) {
            $logger->write(\LOGLEVEL_DEBUG, 'Add new payment for order ' . $shopOrder->kBestellung, $payData);
            $ppMethod->addIncomingPayment($shopOrder, $payData);
            $sendMail = true;
        } elseif ($incommingPayment->cAbgeholt !== 'Y') {
            $logger->write(\LOGLEVEL_DEBUG, 'Update existing payment for order ' . $shopOrder->kBestellung, $payData);
            $this->dropIncommingPayment((int)$incommingPayment->kZahlungseingang);
            $ppMethod->addIncomingPayment($shopOrder, $payData);
        } elseif ($amount->getValue() !== (float)$incommingPayment->fBetrag) {
            $logger->write(\LOGLEVEL_DEBUG, 'Extend existing payment for order ' . $shopOrder->kBestellung, $payData);
            $payData->fBetrag          -= (float)$incommingPayment->fBetrag;
            $payData->fZahlungsgebuehr -= (float)$incommingPayment->fZahlungsgebuehr;
            if ($payData->fBetrag !== 0.0) {
                $ppMethod->addIncomingPayment($shopOrder, $payData);
            }
        }

        $ppMethod->setOrderStatusToPaid($shopOrder);

        if ($sendMail === true) {
            $ppMethod->sendConfirmationMail($shopOrder);
        }
    }

    /**
     * @param Bestellung             $shopOrder
     * @param Capture                $capture
     * @param PayPalPaymentInterface $ppMethod
     * @return void
     */
    public function declinePayment(Bestellung $shopOrder, Capture $capture, PayPalPaymentInterface $ppMethod): void
    {
        $incommingPayment = $this->getIncommingPayment($shopOrder->cBestellNr, $capture->getId());
        if ($incommingPayment === null || $incommingPayment->kZahlungseingang === null) {
            return;
        }

        $logger = $ppMethod->getLogger();
        if ($incommingPayment->cAbgeholt !== 'Y') {
            $logger->write(\LOGLEVEL_DEBUG, 'Decline existing payment', $incommingPayment);
            $this->dropIncommingPayment((int)$incommingPayment->kZahlungseingang);
        } else {
            // TODO inform Wawi that this payment was declined!
            $logger->write(\LOGLEVEL_DEBUG, 'Can not decline existing payment', $incommingPayment);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        if ($ppMethod->getMethod()->nMailSenden & \ZAHLUNGSART_MAIL_STORNO) {
            $ppMethod->sendMail($shopOrder->kBestellung, 'kPlugin_' . $this->plugin->getID() . '_declinepayment');
        }
    }

    /**
     * @param string $url
     * @param int    $code
     * @noinspection PhpNoReturnAttributeCanBeAddedInspection
     */
    public static function redirectAndExit(string $url, int $code = 303): void
    {
        if (\headers_sent()) {
            exit('<script>location.href="' . $url . '"></script>');
        }

        \header('Location: ' . $url, true, $code);
        exit();
    }

    /**
     * @param Order $order
     * @return string
     */
    public function getSanitizedHash(Order $order): string
    {
        $paymentHash = ($paymentHash = $order->getCustomId()) === ''
            ? $order->getPurchase()->getCustomId()
            : $paymentHash;

        return \str_starts_with($paymentHash, '_') ? \substr($paymentHash, 1) : $paymentHash;
    }

    /**
     * @param string $locale
     * @param bool   $posix - use underscore as separator otherwise the minus sign
     * @return string
     */
    public static function sanitizeLocale(string $locale, bool $posix = false): string
    {
        $sep = $posix ? '_' : '-';
        if (\preg_match('/([a-zA-Z]{2})([\-_])?([a-zA-Z]{2})?/', $locale, $hits)) {
            $part[0] = empty($hits[1]) ? $hits[3] ?? 'en' : $hits[1];
            $part[1] = empty($hits[3]) ? $hits[1] ?? 'en' : $hits[3];

            return \strtolower($part[0]) . $sep . \strtoupper($part[1]);
        }

        return 'en' . $sep . 'GB';
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function twoDigitLocale(string $locale): string
    {
        $locale = self::sanitizeLocale($locale);

        return \substr($locale, 0, 2);
    }

    /**
     * @param string $isoCode
     * @return string
     */
    public static function sanitizeISOCode(string $isoCode): string
    {
        if (\mb_strlen($isoCode) === 3) {
            $isoCode = Text::convertISO2ISO639($isoCode);
        }
        if (\mb_strlen($isoCode) !== 2) {
            return 'EN';
        }

        return \mb_strtoupper($isoCode);
    }

    /**
     * @param string $isoCode
     * @return string
     */
    public static function getLocaleFromISO(string $isoCode): string
    {
        static $locales = [
            'AL' => 'en-US',  // ALBANIA
            'DZ' => 'ar-EG',  // ALGERIA
            'AD' => 'en-US',  // ANDORRA
            'AO' => 'en-US',  // ANGOLA
            'AI' => 'en-US',  // ANGUILLA
            'AG' => 'en-US',  // ANTIGUA & BARBUDA
            'AR' => 'es-XC',  // ARGENTINA
            'AM' => 'en-US',  // ARMENIA
            'AW' => 'en-US',  // ARUBA
            'AU' => 'en-AU',  // AUSTRALIA
            'AT' => 'de-DE',  // AUSTRIA
            'AZ' => 'en-US',  // AZERBAIJAN
            'BS' => 'en-US',  // BAHAMAS
            'BH' => 'ar-EG',  // BAHRAIN
            'BB' => 'en-US',  // BARBADOS
            'BY' => 'en-US',  // BELARUS
            'BE' => 'en-US',  // BELGIUM
            'BZ' => 'es-XC',  // BELIZE
            'BJ' => 'fr-XC',  // BENIN
            'BM' => 'en-US',  // BERMUDA
            'BT' => 'en-US',  // BHUTAN
            'BO' => 'es-XC',  // BOLIVIA
            'BA' => 'en-US',  // BOSNIA & HERZEGOVINA
            'BW' => 'en-US',  // BOTSWANA
            'BR' => 'pt-BR',  // BRAZIL
            'VG' => 'en-US',  // BRITISH VIRGIN ISLANDS
            'BN' => 'en-US',  // BRUNEI
            'BG' => 'en-US',  // BULGARIA
            'BF' => 'fr-XC',  // BURKINA FASO
            'BI' => 'fr-XC',  // BURUNDI
            'KH' => 'en-US',  // CAMBODIA
            'CM' => 'fr-XC',  // CAMEROON
            'CA' => 'en-US',  // CANADA
            'CV' => 'en-US',  // CAPE VERDE
            'KY' => 'en-US',  // CAYMAN ISLANDS
            'TD' => 'fr-XC',  // CHAD
            'CL' => 'es-XC',  // CHILE
            'CN' => 'zh-CN',  // CHINA
            'C2' => 'zh-XC',  // CHINA WORLDWIDE
            'CO' => 'es-XC',  // COLOMBIA
            'KM' => 'fr-XC',  // COMOROS
            'CG' => 'en-US',  // CONGO - BRAZZAVILLE
            'CD' => 'fr-XC',  // CONGO - KINSHASA
            'CK' => 'en-US',  // COOK ISLANDS
            'CR' => 'es-XC',  // COSTA RICA
            'CI' => 'fr-XC',  // CÔTE D’IVOIRE
            'HR' => 'en-US',  // CROATIA
            'CY' => 'en-US',  // CYPRUS
            'CZ' => 'en-US',  // CZECH REPUBLIC
            'DK' => 'da-DK',  // DENMARK
            'DJ' => 'fr-XC',  // DJIBOUTI
            'DM' => 'en-US',  // DOMINICA
            'DO' => 'es-XC',  // DOMINICAN REPUBLIC
            'EC' => 'es-XC',  // ECUADOR
            'EG' => 'ar-EG',  // EGYPT
            'SV' => 'es-XC',  // EL SALVADOR
            'ER' => 'en-US',  // ERITREA
            'EE' => 'en-US',  // ESTONIA
            'ET' => 'en-US',  // ETHIOPIA
            'FK' => 'en-US',  // FALKLAND ISLANDS
            'FO' => 'da-DK',  // FAROE ISLANDS
            'FJ' => 'en-US',  // FIJI
            'FI' => 'en-US',  // FINLAND
            'FR' => 'fr-FR',  // FRANCE
            'GF' => 'en-US',  // FRENCH GUIANA
            'PF' => 'en-US',  // FRENCH POLYNESIA
            'GA' => 'fr-XC',  // GABON
            'GM' => 'en-US',  // GAMBIA
            'GE' => 'en-US',  // GEORGIA
            'DE' => 'de-DE',  // GERMANY
            'GI' => 'en-US',  // GIBRALTAR
            'GR' => 'en-US',  // GREECE
            'GL' => 'da-DK',  // GREENLAND
            'GD' => 'en-US',  // GRENADA
            'GP' => 'en-US',  // GUADELOUPE
            'GT' => 'es-XC',  // GUATEMALA
            'GN' => 'fr-XC',  // GUINEA
            'GW' => 'en-US',  // GUINEA-BISSAU
            'GY' => 'en-US',  // GUYANA
            'HN' => 'es-XC',  // HONDURAS
            'HK' => 'en-GB',  // HONG KONG SAR CHINA
            'HU' => 'en-US',  // HUNGARY
            'IS' => 'en-US',  // ICELAND
            'IN' => 'en-GB',  // INDIA
            'ID' => 'id-ID',  // INDONESIA
            'IE' => 'en-US',  // IRELAND
            'IL' => 'he-IL',  // ISRAEL
            'IT' => 'it-IT',  // ITALY
            'JM' => 'es-XC',  // JAMAICA
            'JP' => 'ja-JP',  // JAPAN
            'JO' => 'ar-EG',  // JORDAN
            'KZ' => 'en-US',  // KAZAKHSTAN
            'KE' => 'en-US',  // KENYA
            'KI' => 'en-US',  // KIRIBATI
            'KW' => 'ar-EG',  // KUWAIT
            'KG' => 'en-US',  // KYRGYZSTAN
            'LA' => 'en-US',  // LAOS
            'LV' => 'en-US',  // LATVIA
            'LS' => 'en-US',  // LESOTHO
            'LI' => 'en-US',  // LIECHTENSTEIN
            'LT' => 'en-US',  // LITHUANIA
            'LU' => 'en-US',  // LUXEMBOURG
            'MK' => 'en-US',  // MACEDONIA
            'MG' => 'en-US',  // MADAGASCAR
            'MW' => 'en-US',  // MALAWI
            'MY' => 'en-US',  // MALAYSIA
            'MV' => 'en-US',  // MALDIVES
            'ML' => 'fr-XC',  // MALI
            'MT' => 'en-US',  // MALTA
            'MH' => 'en-US',  // MARSHALL ISLANDS
            'MQ' => 'en-US',  // MARTINIQUE
            'MR' => 'en-US',  // MAURITANIA
            'MU' => 'en-US',  // MAURITIUS
            'YT' => 'en-US',  // MAYOTTE
            'MX' => 'es-XC',  // MEXICO
            'FM' => 'en-US',  // MICRONESIA
            'MD' => 'en-US',  // MOLDOVA
            'MC' => 'fr-XC',  // MONACO
            'MN' => 'en-US',  // MONGOLIA
            'ME' => 'en-US',  // MONTENEGRO
            'MS' => 'en-US',  // MONTSERRAT
            'MA' => 'ar-EG',  // MOROCCO
            'MZ' => 'en-US',  // MOZAMBIQUE
            'NA' => 'en-US',  // NAMIBIA
            'NR' => 'en-US',  // NAURU
            'NP' => 'en-US',  // NEPAL
            'NL' => 'nl-NL',  // NETHERLANDS
            'NC' => 'en-US',  // NEW CALEDONIA
            'NZ' => 'en-US',  // NEW ZEALAND
            'NI' => 'es-XC',  // NICARAGUA
            'NE' => 'fr-XC',  // NIGER
            'NG' => 'en-US',  // NIGERIA
            'NU' => 'en-US',  // NIUE
            'NF' => 'en-US',  // NORFOLK ISLAND
            'NO' => 'no-NO',  // NORWAY
            'OM' => 'ar-EG',  // OMAN
            'PW' => 'en-US',  // PALAU
            'PA' => 'es-XC',  // PANAMA
            'PG' => 'en-US',  // PAPUA NEW GUINEA
            'PY' => 'es-XC',  // PARAGUAY
            'PE' => 'es-XC',  // PERU
            'PH' => 'en-US',  // PHILIPPINES
            'PN' => 'en-US',  // PITCAIRN ISLANDS
            'PL' => 'pl-PL',  // POLAND
            'PT' => 'pt-PT',  // PORTUGAL
            'QA' => 'en-US',  // QATAR
            'RE' => 'en-US',  // RÉUNION
            'RO' => 'en-US',  // ROMANIA
            'RU' => 'ru-RU',  // RUSSIA
            'RW' => 'fr-XC',  // RWANDA
            'WS' => 'en-US',  // SAMOA
            'SM' => 'en-US',  // SAN MARINO
            'ST' => 'en-US',  // SÃO TOMÉ & PRÍNCIPE
            'SA' => 'ar-EG',  // SAUDI ARABIA
            'SN' => 'fr-XC',  // SENEGAL
            'RS' => 'en-US',  // SERBIA
            'SC' => 'fr-XC',  // SEYCHELLES
            'SL' => 'en-US',  // SIERRA LEONE
            'SG' => 'en-GB',  // SINGAPORE
            'SK' => 'en-US',  // SLOVAKIA
            'SI' => 'en-US',  // SLOVENIA
            'SB' => 'en-US',  // SOLOMON ISLANDS
            'SO' => 'en-US',  // SOMALIA
            'ZA' => 'en-US',  // SOUTH AFRICA
            'KR' => 'ko-KR',  // SOUTH KOREA
            'ES' => 'es-ES',  // SPAIN
            'LK' => 'en-US',  // SRI LANKA
            'SH' => 'en-US',  // ST. HELENA
            'KN' => 'en-US',  // ST. KITTS & NEVIS
            'LC' => 'en-US',  // ST. LUCIA
            'PM' => 'en-US',  // ST. PIERRE & MIQUELON
            'VC' => 'en-US',  // ST. VINCENT & GRENADINES
            'SR' => 'en-US',  // SURINAME
            'SJ' => 'en-US',  // SVALBARD & JAN MAYEN
            'SZ' => 'en-US',  // SWAZILAND
            'SE' => 'sv-SE',  // SWEDEN
            'CH' => 'de-DE',  // SWITZERLAND
            'TW' => 'zh-TW',  // TAIWAN
            'TJ' => 'en-US',  // TAJIKISTAN
            'TZ' => 'en-US',  // TANZANIA
            'TH' => 'th-TH',  // THAILAND
            'TG' => 'fr-XC',  // TOGO
            'TO' => 'en-US',  // TONGA
            'TT' => 'en-US',  // TRINIDAD & TOBAGO
            'TN' => 'ar-EG',  // TUNISIA
            'TM' => 'en-US',  // TURKMENISTAN
            'TC' => 'en-US',  // TURKS & CAICOS ISLANDS
            'TV' => 'en-US',  // TUVALU
            'UG' => 'en-US',  // UGANDA
            'UA' => 'en-US',  // UKRAINE
            'AE' => 'en-US',  // UNITED ARAB EMIRATES
            'GB' => 'en-GB',  // UNITED KINGDOM
            'US' => 'en-US',  // UNITED STATES
            'UY' => 'es-XC',  // URUGUAY
            'VU' => 'en-US',  // VANUATU
            'VA' => 'en-US',  // VATICAN CITY
            'VE' => 'es-XC',  // VENEZUELA
            'VN' => 'en-US',  // VIETNAM
            'WF' => 'en-US',  // WALLIS & FUTUNA
            'YE' => 'ar-EG',  // YEMEN
            'ZM' => 'en-US',  // ZAMBIA
            'ZW' => 'en-US',  // ZIMBABWE
            'EN' => 'en-GB',  // EN DEFAULT
        ];

        return $locales[\strtoupper($isoCode)] ?? 'en-GB';
    }
}
