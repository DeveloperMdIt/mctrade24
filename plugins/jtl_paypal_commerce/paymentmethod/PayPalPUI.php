<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use DateInterval;
use DateTime;
use Exception;
use JTL\Alert\Alert;
use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Firma;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\frontend\PUIFrontend;
use Plugin\jtl_paypal_commerce\LegacyHelper;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\PPCPOrderInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\PPCPPUIOrder;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCaptureDecline;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCapturePending;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Amount;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;

/**
 * Class PayPalPUI
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PayPalPUI extends PayPalPayment
{
    /**
     * @inheritDoc
     */
    public function paymentDuringOrderSupported(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function paymentAfterOrderSupported(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return FrontendHandler::getBackendTranslation('Rechnungskauf');
    }

    /**
     * @inheritDoc
     */
    protected function usePurchaseItems(): bool
    {
        return true;
    }

    protected function constructPurchase(): PurchaseUnit
    {
        return new PurchaseUnit(null, true);
    }

    /**
     * @inheritDoc
     */
    protected function isSessionPayed(object $paymentSession): bool
    {
        return empty($paymentSession->txn_id);
    }

    /**
     * @inheritDoc
     */
    public function getFrontendInterface(Configuration $config, JTLSmarty $smarty): PaymentFrontendInterface
    {
        return new PUIFrontend($this->plugin, $this, $smarty);
    }

    /**
     * @return string
     */
    private function getCustomerServiceInstructions(): string
    {
        $csi = $this->plugin->getLocalization()
                     ->getTranslation('pui_customer_service_instructions');
        if (\str_contains($csi, '%s')) {
            $csi = \sprintf($csi, $this->getShopTitle()
                . ' (' . Shop::Container()->getLinkService()->getStaticRoute() . ')');
        }

        return $csi;
    }

    /**
     * @param Bestellung             $order
     * @param PaymentSourceInterface $paymentSource
     * @param Amount                 $amount
     * @return void
     */
    private function updatePUIOrderData(Bestellung $order, PaymentSourceInterface $paymentSource, Amount $amount): void
    {
        $date        = (new DateTime())->add(new DateInterval('P30D')); // hardcoded 30 days
        $company     = new Firma();
        $bankDetails = $paymentSource->getDepositBankDetails();
        $puiData     = [
            '%reference_number%'                  => $paymentSource->getPaymentReference(),
            '%bank_name%'                         => $bankDetails->getBankName(),
            '%account_holder_name%'               => $bankDetails->getAccountHolder(),
            '%international_bank_account_number%' => $bankDetails->getIBAN(),
            '%bank_identifier_code%'              => $bankDetails->getBIC(),
            '%value%'                             => \number_format(
                $amount->getValue(),
                2,
                Frontend::getCurrency()->getDecimalSeparator(),
                ''
            ),
            '%currency%'                          => $amount->getCurrencyCode(),
            '%payment_due_date%'                  => $date->format('d.m.Y'),
            '%company%'                           => $company->cName,
        ];

        $order->cPUIZahlungsdaten = \str_replace(
            \array_keys($puiData),
            \array_values($puiData),
            \sprintf(
                "%s\r\n\r\n%s",
                \str_replace(
                    '<br>',
                    "\r\n",
                    $this->plugin->getLocalization()->getTranslation('jtl_paypal_pui_banktransfer')
                ),
                \str_replace(
                    '<br>',
                    "\r\n",
                    $this->plugin->getLocalization()->getTranslation('jtl_paypal_pui_legal')
                )
            )
        );
        Shop::Container()->getDB()->update('tbestellung', 'kBestellung', $order->kBestellung, (object)[
            'cPUIZahlungsdaten' => $order->cPUIZahlungsdaten,
            'cAbgeholt'         => 'N',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        if (!PayPalPayment::isValidIntern($args_arr)) {
            return false;
        }

        $puiAvail = (int)$this->config->getPrefixedConfigItem('PaymentPUIAvail', '0');
        $conf     = Shop::getSettings([\CONF_KUNDEN]);

        if (
            !($args_arr['checkConnectionOnly'] ?? false)
            && (
                $conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'N'
                || $conf['kunden']['kundenregistrierung_abfragen_tel'] === 'N'
            )
        ) {
            $puiAvail = 0;
        }
        try {
            return $puiAvail > 0
                && $this->method->getDuringOrder()
                && Token::getInstance()->getToken() !== null;
        } catch (AuthorizationException $e) {
            $this->getLogger()->write(\LOGLEVEL_ERROR, 'AuthorizationException:' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        if (parent::isValid($customer, $cart)) {
            $cartTotal = $cart->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true);
            if (!($customer instanceof Customer)) {
                $customer = new Customer($customer->kKunde);
            }
            $customerGroup = new CustomerGroup($customer->getGroupID());

            return (
                !$customerGroup->isMerchant()
                && $customer->cLand === 'DE'
                && $cartTotal >= 5 && $cartTotal <= 2500
            );
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressPayment(object $customer, Cart $cart): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressProduct(object $customer, ?Artikel $product): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerPayment(object $customer, Cart $cart): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerProduct(object $customer, ?Artikel $product): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry
    {
        $entry = parent::getBackendNotification($plugin, $force);
        if ($entry !== null) {
            return $entry;
        }

        if (!($force || $this->isAssigned())) {
            return null;
        }

        $puiAvail = (int)$this->config->getPrefixedConfigItem('PaymentPUIAvail', '0');
        if ($puiAvail === 0) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_DANGER,
                \__($this->method->getName()),
                \__('Rechnungskauf wird von Ihrem PayPal-Account nicht unterstützt.'),
                Shop::getAdminURL() . '/plugin/' . $this->plugin->getID()
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        $conf = Shop::getSettings([\CONF_KUNDEN]);
        if (
            $conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'N'
            || $conf['kunden']['kundenregistrierung_abfragen_tel'] === 'N'
        ) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_DANGER,
                \__($this->method->getName()),
                \__('Angabe von Telefonnummer und Geburtsdatum ist erforderlich'),
                Shop::getAdminURL() . '/config/' . \CONF_KUNDEN
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function renderBackendInformation(JTLSmarty $smarty, PluginInterface $plugin): void
    {
        PayPalPayment::renderBackendInformation($smarty, $plugin);

        if (!$this->isAssigned() || !$this->config->getConfigValues()->isAuthConfigured()) {
            return;
        }

        $puiAvail = (int)$this->config->getPrefixedConfigItem('PaymentPUIAvail', '0');
        if ($puiAvail === 0) {
            $this->helper->getAlert()->addDanger(
                \sprintf(
                    \__('Die Zahlungsart Rechnungskauf wird von Ihrem PayPal-Account nicht unterstützt'),
                    '<strong>' . \__($this->method->getName()) . '</strong>'
                ),
                'puiNotSupported',
                [
                    'showInAlertListTemplate' => false,
                ]
            );

            return;
        }

        $conf = Shop::getSettings([\CONF_KUNDEN]);
        if (
            $conf['kunden']['kundenregistrierung_abfragen_geburtstag'] === 'N'
            || $conf['kunden']['kundenregistrierung_abfragen_tel'] === 'N'
        ) {
            $this->helper->getAlert()->addWarning(
                \sprintf(
                    \__('Für %s sind zusätzliche Angaben erforderlich'),
                    '<strong>' . \__($this->method->getName()) . '</strong><br>'
                ),
                'puiNotSupported',
                [
                    'showInAlertListTemplate' => false,
                ]
            );

            return;
        }

        $puiLimit = (int)$this->config->getPrefixedConfigItem('PaymentPUILimit', '0');
        if ($puiLimit === 1) {
            $this->getLogger()->write(\LOGLEVEL_NOTICE, \sprintf(
                \__('Die Zahlungsart "%s" steht momentan nur eingeschränkt zur Verfügung'),
                \__($this->method->getName())
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function validatePayerData(Customer $customer, ?Lieferadresse $shippingAdr = null, ?Cart $cart = null): void
    {
        parent::validatePayerData($customer, $shippingAdr, $cart);

        $puiLegalInfoShown = Request::postVar('puiLegalInfoShown');
        if ($puiLegalInfoShown !== null) {
            $this->sessionCache->set('puiLegalInfoShown', $puiLegalInfoShown);
        }

        if ($this->sessionCache->getInt('puiLegalInfoShown') !== 1) {
            throw new InvalidPayerDataException(
                FrontendHandler::getBackendTranslation('Bitte bestätigen Sie die Rechtlichen Informationen.'),
                Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
                . '?editVersandart=1'
            );
        }
        $customerGroup = new CustomerGroup($customer->getGroupID());
        if ($customerGroup->isMerchant()) {
            throw new InvalidPayerDataException(
                \sprintf(
                    FrontendHandler::getBackendTranslation('%s ist nur für Privatkunden verfügbar'),
                    $this->getMethod()->getName()
                ),
                Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
                . '?editRechnungsadresse=1'
            );
        }

        if ($customer->cLand !== 'DE') {
            throw new InvalidPayerDataException(
                \sprintf(
                    FrontendHandler::getBackendTranslation(
                        '%s ist nur mit einer Rechnungsanschrift in Deutschland verfügbar'
                    ),
                    $this->getMethod()->getName()
                ),
                Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
                . '?editRechnungsadresse=1'
            );
        }

        $currency = Frontend::getCurrency();
        if ($currency->getCode() !== 'EUR') {
            throw new InvalidPayerDataException(
                \sprintf(
                    FrontendHandler::getBackendTranslation('%s ist nur in Euro möglich.'),
                    $this->getMethod()->getName()
                ),
                $this->getPaymentCancelURL()
            );
        }

        $exception = new InvalidPayerDataException();
        if (empty($customer->cTel)) {
            $exception->addAlert(new Alert(Alert::TYPE_ERROR, \sprintf(
                FrontendHandler::getBackendTranslation('Für %s ist die Angabe einer Telefonnummer erforderlich'),
                $this->getMethod()->getName()
            ), 'confirmPUI_tel', ['saveInSession' => true]));
        }
        try {
            $birthDate = new DateTime($customer->dGeburtstag ?? '');
        } catch (Exception) {
            $birthDate = null;
        }
        if (empty($customer->dGeburtstag) || $birthDate === null) {
            $exception->addAlert(new Alert(Alert::TYPE_ERROR, \sprintf(
                FrontendHandler::getBackendTranslation('Für %s ist die Angabe des Geburtsdatums erforderlich'),
                $this->getMethod()->getName()
            ), 'confirmPUI_birthdate', ['saveInSession' => true]));
        }

        if ($exception->hasAlerts()) {
            throw $exception->setRedirectURL(
                Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php') . '?editRechnungsadresse=1'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool
    {
        $mi = $this->getMerchantIntegration($this->config);
        if ($mi === null) {
            return false;
        }

        $paymentProduct = $mi->getProductByName('PAYMENT_METHODS');
        $puiAvail       = false;
        $limited        = false;
        if ($paymentProduct !== null && \in_array('PAY_UPON_INVOICE', $paymentProduct->getCapabilities(), true)) {
            $pui      = $mi->getCapabilityByName('PAY_UPON_INVOICE');
            $puiAvail = $pui !== null && $pui->isActive();
            $limited  = $puiAvail && $pui->hasLimits();
        }
        if (!$onlyCheck) {
            $this->config->saveConfigItems([
                'PaymentPUIAvail' => $puiAvail ? '1' : '0',
                'PaymentPUILimit' => $limited ? '1' : '0',
            ]);
        }

        return $puiAvail;
    }

    /**
     * @inheritDoc
     */
    protected function validateFundingSource(string $fundingSource): string
    {
        return $fundingSource === $this->getFundingSource() ? $fundingSource : '';
    }

    /**
     * @inheritDoc
     */
    public function setFundingSource(string $fundingSource): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getDefaultFundingSource(): string
    {
        return PaymentSourceBuilder::FUNDING_PUI;
    }

    /**
     * @inheritDoc
     */
    protected function constructOrder(
        Customer $customer,
        Cart $cart,
        string $shippingContext,
        string $payAction,
        string $orderHash
    ): Order {
        $paymentSource = (new PaymentSourceBuilder($this->getFundingSource()))->build();
        $paymentSource->applyPayer($this->createPayer($customer, Payer::PAYER_ALL))
                      ->setExperienceContext($this->createExperienceContext(
                          $paymentSource,
                          $customer->kSprache ?? Shop::Lang()->currentLanguageID,
                          $shippingContext,
                          $payAction
                      )->setCustomerServiceInstruction($this->getCustomerServiceInstructions()));
        return (new Order())
            ->addPurchase($this->createPurchase($orderHash, Frontend::getDeliveryAddress(), $cart)
                               ->setInvoiceId(LegacyHelper::baueBestellnummer()))
            ->setIntent(Order::INTENT_CAPTURE)
            ->setProcessingInstruction(Order::PI_AUTO_COMPLETE)
            ->setPaymentSource($this->getFundingSource(), $paymentSource);
    }

    /**
     * @inheritDoc
     */
    protected function createPPCPOrder(Order $createOrder, string $bnCode): PPCPOrderInterface
    {
        return ($this->ppcpOrder = PPCPPUIOrder::create($createOrder, $bnCode, $this->getLogger()));
    }

    /**
     * @inheritDoc
     */
    protected function loadPPCPOrder(string $orderId): PPCPOrderInterface
    {
        return ($this->ppcpOrder = PPCPPUIOrder::load($orderId, $this->getLogger()));
    }

    /**
     * @inheritDoc
     */
    protected function isValidOrderState(Order $order, string $state): bool
    {
        $orderState = $order->getStatus();

        if ($state === OrderStatus::STATUS_CREATED) {
            return \in_array($orderState, [
                OrderStatus::STATUS_CREATED,
                OrderStatus::STATUS_APPROVED,
                OrderStatus::STATUS_COMPLETED,
                OrderStatus::STATUS_PENDING_APPROVAL
            ], true);
        }

        return parent::isValidOrderState($order, $state);
    }

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess(Bestellung $order): void
    {
        parent::preparePaymentProcess($order);

        $ppOrder = $this->getPPOrder($this->createPPOrder(
            Frontend::getCustomer(),
            Frontend::getCart(),
            PaymentSourceBuilder::FUNDING_PUI,
            ExperienceContext::SHIPPING_PROVIDED,
            ExperienceContext::USER_ACTION_PAY_NOW
        ));
        if ($ppOrder === null || empty($ppOrder->getId())) {
            $this->getLogger()->write(
                \LOGLEVEL_NOTICE,
                'preparePaymentProcess: payment can not be processed, order does not exists'
            );
            $this->raisePaymentError('jtl_paypal_commerce_payment_error', $this->getPaymentRetryURL($ppOrder));
            exit();
        }

        $ppOrder = (new TCCaptureDecline())->execute(
            $this,
            (new TCCapturePending())->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart()),
            Frontend::getCustomer(),
            Frontend::getCart()
        );
        if (
            $this->isValidOrderState($ppOrder, OrderStatus::STATUS_PENDING)
            || $this->isValidOrderState($ppOrder, OrderStatus::STATUS_APPROVED)
            || $this->isValidOrderState($ppOrder, OrderStatus::STATUS_COMPLETED)
        ) {
            $this->helper->persistOrder($order, $ppOrder, $this, [
                'invoiceId' => $ppOrder->getInvoiceId()
            ]);
        }

        $this->handleOrder($ppOrder, $order);
    }

    /**
     * @inheritDoc
     */
    public function addIncomingPayment(Bestellung $order, object $payment)
    {
        if ($payment instanceof Order) {
            $paymenSource = $payment->getPaymentSource(PaymentSourceBuilder::FUNDING_PUI);
            if ($paymenSource !== null) {
                $payment->setPayer($paymenSource->fetchPayer());
            }
        }

        return parent::addIncomingPayment($order, $payment);
    }

    /**
     * @inheritDoc
     */
    public function sendConfirmationMail(Bestellung $order): void
    {
        $this->sendMail($order->kBestellung, 'kPlugin_' . $this->plugin->getID() . '_paymentinformation');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentRetryURL(?Order $ppOrder): string
    {
        return $this->sessionCache->getInt('puiLegalInfoShown') !== 1
            ? $this->getPaymentCancelURL($ppOrder)
            : parent::getPaymentRetryURL($ppOrder);
    }

    /**
     * @inheritDoc
     */
    public function onPaymentState(PaymentStateResult $result, bool $timeOut = false): void
    {
        parent::onPaymentState($result, $timeOut);

        if ($result->getState() === OrderStatus::STATUS_PENDING) {
            $result->setCompleteMessage($this->plugin->getLocalization()->getTranslation('pui_pending_approval_info'));
        } elseif ($result->getState() === OrderStatus::STATUS_APPROVED) {
            $result->setRedirect($this->getPaymentStateURL($this->getPPOrder()));
        }
    }

    /**
     * @inheritDoc
     */
    public function onPaymentComplete(Order $order): void
    {
        parent::onPaymentComplete($order);

        $paymentSource = $order->getPaymentSource(PaymentSourceBuilder::FUNDING_PUI);
        $shopOrder     = $this->helper->getShopOrder($order);

        if ($paymentSource === null || $shopOrder === null) {
            return;
        }

        $capture = $order->getPurchase()->getCapture();
        if ($capture !== null) {
            $this->updatePUIOrderData($shopOrder, $paymentSource, $capture->getAmount());
        }
    }

    /**
     * @inheritDoc
     */
    public function handleOrder(Order $order, ?Bestellung $shopOrder = null, bool $return = false): void
    {
        $orderExists = $shopOrder !== null && $this->helper->existsOrder($shopOrder);
        if ($this->isValidOrderState($order, OrderStatus::STATUS_COMPLETED)) {
            $paymentSource = $order->getPaymentSource(PaymentSourceBuilder::FUNDING_PUI);
            if ($paymentSource !== null && $orderExists) {
                $capture = $order->getPurchase()->getCapture();
                $amount  = $capture !== null ? $capture->getAmount() : $order->getPurchase()->getAmount();
                $this->updatePUIOrderData($shopOrder, $paymentSource, $amount);
                $this->helper->getAlert()->addInfo(
                    \nl2br($shopOrder->cPUIZahlungsdaten),
                    'paymentInformation'
                );
            } else {
                $this->getLogger()->write(\LOGLEVEL_ERROR, 'handleOrder: paymentSource is empty');
            }
            parent::handleOrder($order, $shopOrder, $return);

            return;
        }

        parent::handleOrder($order, $shopOrder, $return);
        if ($this->isValidOrderState($order, OrderStatus::STATUS_APPROVED)) {
            /* on pui this is next step after PENDING_APPROVAL - should change to COMPLETED or DECLINED */
            if (!$orderExists) {
                // payment is pending but shop order failed => create shop order and goto notification page
                $this->helper->persistOrder($shopOrder, $order, $this, []);
            }
            $order->setLink((object)[
                'rel'  => 'paymentRedirect',
                'href' => $this->getPaymentStateURL($order),
            ]);
        }
    }
}
