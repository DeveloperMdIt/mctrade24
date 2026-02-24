<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use Exception;
use InvalidArgumentException;
use JsonException;
use JTL\Checkout\Bestellung;
use JTL\DB\DbInterface;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\IO\IO;
use JTL\IO\IOError;
use JTL\IO\IOResponse;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\frontend\ControllerFactory;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\ShippingChangeResponse;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

/**
 * Class IOHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class IOHandler
{
    private PluginInterface $plugin;
    private DbInterface $db;
    private AlertServiceInterface $alertService;

    /**
     * IOHandler constructor
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?AlertServiceInterface $alertService = null
    ) {
        $this->plugin       = $plugin;
        $this->db           = $db ?? Shop::Container()->getDB();
        $this->alertService = $alertService ?? Shop::Container()->getAlertService();
    }

    public function ioRequest(array $args): void
    {
        /** @var IO $io */
        $io = $args['io'];
        try {
            $data = \json_decode($args['request'], false, 512, \JSON_THROW_ON_ERROR);
            if (!\str_starts_with($data->name, 'jtl_paypal_commerce.')) {
                return;
            }

            if (!Form::validateToken($data->csrf ?? null)) {
                throw new InvalidArgumentException('CSRF validation failed');
            }

            $io->register('jtl_paypal_commerce.checkPaymentState', [$this, 'checkIOPaymentState']);
            $io->register('jtl_paypal_commerce.createOrder', [$this, 'createIOPPOrder']);
            $io->register('jtl_paypal_commerce.shippingChange', [$this, 'shippingIOChange']);
            $io->register('jtl_paypal_commerce.logState', [$this, 'logIOState']);
        } catch (Exception $e) {
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(
                \LOGLEVEL_NOTICE,
                $this->plugin->getPluginID() . '::ioRequest - can not register io handler (' . $e->getMessage() . ')'
            );
        }
    }

    public function checkIOPaymentState(int $methodID, bool $timeout): object
    {
        $result    = new IOResponse();
        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromID($methodID);
        if ($payMethod === null) {
            $result->setClientRedirect(
                Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?bestellungen=1'
            );

            return $result;
        }

        $helper       = Helper::getInstance($this->plugin);
        $stateHandler = new PaymentStateHandler($this->plugin, $this->db, $this->alertService);
        $paymentState = $stateHandler->getPaymentStateResult($payMethod, $timeout);
        $ppOrder      = $payMethod->getPPOrder();
        $shopOrder    = $ppOrder !== null ? $helper->getShopOrder($ppOrder) : null;
        if ($paymentState === null) {
            $result->setClientRedirect($payMethod->getReturnURL($shopOrder ?? new Bestellung()));

            return $result;
        }
        if ($paymentState->hasRedirect() && $paymentState->getRedirect() !== $payMethod->getPaymentStateURL($ppOrder)) {
            if ($paymentState->hasCompleteMessage()) {
                $this->alertService->addInfo(
                    $paymentState->getCompleteMessage(),
                    'paymentState'
                );
            }
            $result->setClientRedirect($paymentState->getRedirect());
        } else {
            $result->assignDom('pp-loading-body span', 'innerHTML', $paymentState->hasPendingMessage()
                ? $paymentState->getPendingMessage()
                : FrontendHandler::getBackendTranslation('Ihre Zahlung wird überprüft'));
        }

        return $result;
    }

    private function checkVaulting(PayPalPaymentInterface $paymentMethod, string $fundingSource, ?array $formData): bool
    {
        if ($formData === null) {
            return false;
        }

        $vaultingHelper  = new VaultingHelper(PPCHelper::getConfiguration($this->plugin));
        $vaultingChecked = $formData['ppc_vaulting_enable'] ?? [];
        $vaultingHelper->enableVaulting(
            $fundingSource,
            $paymentMethod,
            (int)($vaultingChecked[$fundingSource] ?? 0) > 0
        );

        return ((int)($formData['ppc_vaulting_active'] ?? 0) > 0)
            && $vaultingHelper->isVaultingEnabled($fundingSource);
    }

    public function createIOPPOrder(
        string $fundingSource,
        ?string $payment = null,
        ?string $bnCode = null,
        ?array $formData = null
    ): object {
        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromName($payment ?? 'PayPalCommerce');
        if ($payMethod === null) {
            return new IOError('Paymentmethod not found');
        }

        $result = new IOResponse();
        $payMethod->unsetCache();
        $payMethod->setFundingSource($fundingSource);
        $isVaultingActive = $this->checkVaulting($payMethod, $fundingSource, $formData);
        $shippingMethodId = (int)($formData['Versandart'] ?? 0);
        if (
            ($formData !== null)
            && ((int)($formData['versandartwahl'] ?? 0) === 1 || $shippingMethodId > 0)
            && !ControllerFactory::getCheckoutController()->checkShippingSelection($shippingMethodId, $formData)
        ) {
            $alert = $this->alertService->getAlert('fillShipping');
            if ($alert !== null) {
                $this->alertService->removeAlertByKey('fillShipping');
                $result->assignVar('createResult', $alert->getMessage());
            } else {
                $result->assignVar('createResult', Shop::Lang()->get('fillShipping', 'checkout'));
            }

            return $result;
        }

        $ppcOrderId = $payMethod->createPPOrder(
            Frontend::getCustomer(),
            Frontend::getCart(),
            $fundingSource,
            $isVaultingActive ? ExperienceContext::SHIPPING_PROVIDED : ExperienceContext::SHIPPING_FROM_FILE,
            $isVaultingActive ? ExperienceContext::USER_ACTION_PAY_NOW : ExperienceContext::USER_ACTION_CONTINUE,
            $bnCode ?? MerchantCredentials::BNCODE_EXPRESS
        );
        if ($ppcOrderId === null) {
            $logger = new Logger(Logger::TYPE_PAYMENT, $payMethod);
            $logger->write(\LOGLEVEL_NOTICE, 'createIOPPOrder - PayPal order can not be created.');

            $alert = $this->alertService->getAlert('createOrderRequest');
            if ($alert !== null) {
                $result->assignVar('createResultDetails', $alert->getMessage());
                unset($_SESSION['alerts']['createOrderRequest']);
            }
            $result->assignVar(
                'createResult',
                FrontendHandler::getBackendTranslation('Die Zahlung konnte nicht bei PayPal angefragt werden')
            );
        } else {
            $result->assignVar('orderId', $ppcOrderId);
        }

        return $result;
    }

    public function shippingIOChange(array $data, ?string $payment = null): object
    {
        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromName($payment ?? 'PayPalCommerce');
        if ($payMethod === null) {
            return new IOError('Paymentmethod not found');
        }

        $result        = new IOResponse();
        $ppOrder       = $payMethod->getPPOrder();
        $patchShipping = false;
        try {
            $objData  = \json_encode($data, \JSON_FORCE_OBJECT | \JSON_THROW_ON_ERROR);
            $shipping = new ShippingChangeResponse(\json_decode($objData, false, 128, \JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(
                \LOGLEVEL_ERROR,
                $this->plugin->getPluginID() . '::shippingIOChange - JSON-Error (' . $e->getMessage() . ')'
            );
            $shipping = null;
        }
        if ($ppOrder !== null && $shipping !== null && $ppOrder->getId() === $shipping->getOrderID()) {
            $address = $shipping->getShippingAddress();
            if ($address !== null) {
                $patch = $payMethod->handleShippingData($ppOrder, $shipping);
                if ($patch !== null) {
                    $patchShipping = true;
                    $result->assignVar('op', $patch->getOp())
                           ->assignVar('path', $patch->getPath())
                           ->assignVar('value', $patch->getValue());
                }
            }
        }

        return $result->assignVar('patch', $patchShipping);
    }

    public function logIOState(int $logLevel, string $message, ?array $params = null): object
    {
        $payment   = ($params ?? [])['paymentClass'] ?? 'PayPalCommerce';
        $payMethod = Helper::getInstance($this->plugin)->getPaymentFromName($payment);
        if ($payMethod === null) {
            return new IOError('Paymentmethod not found');
        }

        $payMethod->getLogger()->write($logLevel, Text::xssClean($message), $params['data'] ?? null);

        return new IOResponse();
    }
}
