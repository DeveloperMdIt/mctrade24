<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use JTL\Checkout\Bestellung;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use Plugin\jtl_paypal_commerce\frontend\AccountPage;
use Plugin\jtl_paypal_commerce\frontend\CheckoutPage;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class PageHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class PageHandler
{
    private PluginInterface $plugin;
    private DbInterface $db;
    private Configuration $config;
    private AlertServiceInterface $alertService;

    /**
     * PageHandler constructor
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?Configuration $configuration = null,
        ?AlertServiceInterface $alertService = null
    ) {
        $this->plugin       = $plugin;
        $this->db           = $db ?? Shop::Container()->getDB();
        $this->config       = $configuration ?? PPCHelper::getConfiguration($plugin);
        $this->alertService = $alertService ?? Shop::Container()->getAlertService();
    }

    public function pageSetPageType(array $args): void
    {
        if ($args['pageType'] !== \PAGE_BESTELLSTATUS) {
            return;
        }

        $uid = Request::verifyGPDataString('uid');
        if ($uid === '') {
            return;
        }

        $maxAttempts = Shopsetting::getInstance($this->db, Shop::Container()->getCache())->getValue(
            \CONF_KUNDEN,
            'kundenlogin_max_loginversuche'
        );
        $orderId     = $this->db->getSingleInt(
            'SELECT kBestellung
                FROM tbestellstatus
                WHERE dDatum >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND cUID = :uid
                    AND (failedAttempts <= :maxAttempts OR 1 = :loggedIn)',
            'kBestellung',
            [
                'uid'         => $uid,
                'maxAttempts' => $maxAttempts,
                'loggedIn'    => Frontend::getCustomer()->isLoggedIn() ? 1 : 0,
            ]
        );
        $shopOrder   = new Bestellung($orderId, true);
        if ($shopOrder->kBestellung === 0) {
            return;
        }

        CheckoutPage::getInstance($this->plugin)->finishOrderFromStatePage($shopOrder);
    }

    public function pageStepShipping(): void
    {
        CheckoutPage::getInstance($this->plugin)->setPageStep(CheckoutPage::STEP_SHIPPING);
    }

    /**
     * @return void
     */
    public function pageStepPayment(): void
    {
        CheckoutPage::getInstance($this->plugin)->validatePayment(Shop::Smarty());
    }

    /**
     * @return void
     */
    public function pageStepConfirm(): void
    {
        CheckoutPage::getInstance($this->plugin)->setPageStep(CheckoutPage::STEP_CONFIRM);
    }

    /**
     * @param array $args
     * @return void
     */
    public function pageStepFinish(array $args): void
    {
        $alert = $this->alertService->getAlert('paymentState');
        if ($alert !== null && $alert->getMessage() === Order::PI_AUTO_COMPLETE) {
            $this->alertService->removeAlertByKey('paymentState');
        }

        CheckoutPage::getInstance($this->plugin)->finishOrder($args['oBestellung']);
    }

    /**
     * @return void
     */
    public function pageStepAddress(): void
    {
        CheckoutPage::getInstance($this->plugin)->setPageStep(CheckoutPage::STEP_ADDRESS);
    }

    /**
     * @return void
     */
    public function pageStepProductDetails(): void
    {
        CheckoutPage::getInstance($this->plugin)->setPageStep(CheckoutPage::PRODUCT_DETAILS);
    }

    /**
     * @return void
     */
    public function pageStepCart(): void
    {
        CheckoutPage::getInstance($this->plugin)->setPageStep(CheckoutPage::CART);
    }

    public function pageCustomerAccount(): void
    {
        if (Request::getInt('bestellung') === 0) {
            $accountPage = AccountPage::getInstance($this->plugin);
            $accountPage->setPageStep(AccountPage::STEP_OVERVIEW);

            return;
        }

        $order = Shop::Smarty()->getTemplateVars('Bestellung');
        if ((int)($order->kBestellung ?? 0) === 0) {
            return;
        }
        $helper    = Helper::getInstance($this->plugin);
        $payMethod = $helper->getPaymentFromID($order->kZahlungsart);
        if ($payMethod === null) {
            return;
        }

        $payMethod->getFrontendInterface($this->config, Shop::Smarty())->renderOrderDetailPage($order, $payMethod);
    }
}
