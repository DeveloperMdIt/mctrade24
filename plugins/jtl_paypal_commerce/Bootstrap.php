<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce;

use Exception;
use JTL\Backend\AdminIO;
use JTL\Backend\Notification;
use JTL\Backend\NotificationEntry;
use JTL\Backend\Permissions;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Link\LinkInterface;
use JTL\Plugin\Bootstrapper;
use JTL\Plugin\Plugin;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\Controller;
use Plugin\jtl_paypal_commerce\adminmenu\Handler as BackendHandler;
use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;
use Plugin\jtl_paypal_commerce\adminmenu\Renderer;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;
use Plugin\jtl_paypal_commerce\CronJob\CronHelper;
use Plugin\jtl_paypal_commerce\frontend\CheckoutPage;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\IOHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\OrderHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\PageHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\PaymentStateHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\SyncHandler;
use Plugin\jtl_paypal_commerce\frontend\Handler\WebhookHandler;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\BackendUIsettings;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;

/**
 * Class Bootstrap
 * @package Plugin\jtl_paypal_commerce
 * @uses    Migration20210318124506
 * @uses    Migration20220405150932
 * @uses    Migration20220923124000
 * @uses    Migration20230908142000
 * @uses    Migration20240703174519
 * @uses    Migration20241014143300
 * @uses    Migration20241113112000
 * @uses    Migration20250109094500
 * @uses    Migration20250227105200
 * @uses    Migration20250303140000
 * @uses    Migration20250517094500
 * @uses    Migration20250521103600
 * @uses    Migration20250624150600
 * @uses    Migration20250922073000
 */
class Bootstrap extends Bootstrapper
{
    /** @var AlertServiceInterface|null */
    private ?AlertServiceInterface $alertService = null;

    /**
     * @return AlertServiceInterface
     */
    private function getAlert(): AlertServiceInterface
    {
        if ($this->alertService === null) {
            $this->alertService = AlertService::getInstance();
        }

        return $this->alertService;
    }

    /**
     * @param Dispatcher    $dispatcher
     * @param Configuration $configuration
     * @return void
     */
    private function initFrontend(Dispatcher $dispatcher, Configuration $configuration): void
    {
        $plugin          = $this->getPlugin();
        $db              = $this->getDB();
        $alert           = $this->getAlert();
        $frontendHandler = new FrontendHandler($plugin, $db, $configuration, $alert, $this->getCache());
        $ioHandler       = new IOHandler($plugin, $db, $alert);
        $orderHandler    = new OrderHandler($plugin, $db);
        $pageHandler     = new PageHandler($plugin, $db, $configuration, $alert);
        $syncHandler     = new SyncHandler($plugin, $db);
        $preHook         = 'shop.hook.';
        $dispatcher->listen($preHook . \HOOK_BESTELLVORGANG_PAGE_STEPVERSAND, [$pageHandler, 'pageStepShipping']);
        $dispatcher->listen($preHook . \HOOK_BESTELLVORGANG_PAGE_STEPZAHLUNG, [$pageHandler, 'pageStepPayment']);
        $dispatcher->listen($preHook . \HOOK_BESTELLVORGANG_PAGE_STEPLIEFERADRESSE, [$pageHandler, 'pageStepAddress']);
        $dispatcher->listen($preHook . \HOOK_BESTELLVORGANG_PAGE_STEPBESTAETIGUNG, [$pageHandler, 'pageStepConfirm']);
        $dispatcher->listen($preHook . \HOOK_BESTELLABSCHLUSS_PAGE, [$pageHandler, 'pageStepFinish']);
        $dispatcher->listen($preHook . \HOOK_ARTIKEL_PAGE, [$pageHandler, 'pageStepProductDetails']);
        $dispatcher->listen($preHook . \HOOK_WARENKORB_PAGE, [$pageHandler, 'pageStepCart']);
        $dispatcher->listen($preHook . \HOOK_SHOP_SET_PAGE_TYPE, [$pageHandler, 'pageSetPageType']);
        $dispatcher->listen($preHook . \HOOK_JTL_PAGE, [$pageHandler, 'pageCustomerAccount']);

        $dispatcher->listen($preHook . \HOOK_SMARTY_OUTPUTFILTER, [$frontendHandler, 'smarty']);
        $dispatcher->listen($preHook . \CONSENT_MANAGER_GET_ACTIVE_ITEMS, [$frontendHandler, 'addConsentItem']);
        $dispatcher->listen($preHook . \HOOK_ROUTER_PRE_DISPATCH, [$frontendHandler, 'routerPredispatch']);

        $dispatcher->listen($preHook . \HOOK_IO_HANDLE_REQUEST, [$ioHandler, 'ioRequest']);

        $dispatcher->listen($preHook . \HOOK_BESTELLUNGEN_XML_BEARBEITESET, [$orderHandler, 'updateOrder']);
        $dispatcher->listen($preHook . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, [$orderHandler, 'saveOrder']);
        $dispatcher->listen($preHook . \HOOK_DELIVERYNOTES_XML_SHIPPING, [$orderHandler, 'addTracking']);

        $dispatcher->listen('shop.hook.' . \HOOK_LASTJOBS_HOLEJOBS, [$syncHandler, 'lastJobs']);
    }

    /**
     * @param BackendHandler $handler
     * @param Dispatcher     $dispatcher
     * @return void
     */
    private function initBackend(BackendHandler $handler, Dispatcher $dispatcher): void
    {
        $plugin = $this->getPlugin();
        $dispatcher->listen(
            'shop.hook.' . \HOOK_IO_HANDLE_REQUEST_ADMIN,
            static function (array $args) use ($handler) {
                /** @var AdminIO $io */
                $io = $args['io'];
                $io->register('jtl_ppc_infos_handleAjax', [$handler, 'handleAjax']);
                $io->register('jtl_ppc_carrier_mapping', [$handler, 'handleCarrierMapping']);
                $io->register('jtl_ppc_orderstate', [$handler, 'handleOrderState']);
                $io->register('jtl_ppc_listActionGet', [$handler, 'handlelistActionGet']);
                $io->register('jtl_ppc_listActionPost', [$handler, 'handlelistActionPost']);
                $io->register('jtl_ppc_resetCredentials', [$handler, 'handleShowResetCredentials']);
                $io->register('jtl_ppc_resetCredentials_sendMail', [$handler, 'handleShowResetCredentialsMail']);
            }
        );
        $dispatcher->listen('backend.notification', [$this, 'checkPaymentNotifications']);
        $task = Request::postVar('task');
        if (
            Form::validateToken() && (
                \str_contains($_SERVER['SCRIPT_FILENAME'], '/zahlungsarten.php') !== false ||
                \str_contains($_SERVER['REDIRECT_URL'] ?? $_SERVER['SCRIPT_NAME'], 'paymentmethods') !== false)
        ) {
            Controller::getInstance($plugin, $this->getDB(), $this->getAlert())->run('checkPayment');
        } elseif (
            $task !== null && Form::validateToken()
            && Request::postInt('kPlugin', Request::getInt('kPlugin')) === $plugin->getID()
        ) {
            Controller::getInstance($plugin, $this->getDB(), $this->getAlert())->run($task);
        }
    }

    /**
     * @inheritDoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        $plugin = $this->getPlugin();
        require_once $plugin->getPaths()->getBasePath() . 'vendor/autoload.php';
        $this->registerContainer();

        $configuration = PPCHelper::getConfiguration($plugin);
        $cronHelper    = CronHelper::getInstance($configuration);
        $dispatcher->listen(Event::MAP_CRONJOB_TYPE, [$cronHelper, 'mappingCronjobType']);
        $dispatcher->listen(Event::GET_AVAILABLE_CRONJOBS, [$cronHelper, 'availableCronjobType']);
        if (Shop::isFrontend()) {
            $this->initFrontend($dispatcher, $configuration);
        } else {
            $account    = Shop::Container()->getAdminAccount();
            $permission = $account->logged() &&
                ($account->permission(Permissions::PLUGIN_DETAIL_VIEW_ALL)
                    || $account->permission(Permissions::PLUGIN_DETAIL_VIEW_ID . $plugin->getID())
                );
            if ($permission) {
                $this->initBackend(new BackendHandler($plugin, $this->getDB()), $dispatcher);
            }
        }
    }

    /**
     * writes the default settings to the DB during installation,
     * as defined in @see BackendUIsettings
     */
    public function installed(): void
    {
        parent::installed();
        $config = Configuration::getInstance($this->getPlugin(), Shop::Container()->getDB());

        try {
            $settingArray = (BackendUIsettings::getDefaultSettings())->toArray();
        } catch (Exception) {
            $settingArray = [];
        }
        $defaultSettings = [];
        foreach ($settingArray as $settingName => $setting) {
            if ($setting['value'] !== '') {
                $defaultSettings[$settingName] = $setting['value'];
            }
        }
        $config->saveConfigItems($defaultSettings);
    }

    /**
     * @inheritDoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
        parent::uninstalled($deleteData);

        CronHelper::dropCron();
    }

    /**
     * @return void
     */
    protected function registerContainer(): void
    {
        $container = Shop::Container();
        try {
            $container->setSingleton(Configuration::class, function () {
                return PPCHelper::getConfiguration($this->getPlugin(), true);
            });
            $container->setSingleton(EnvironmentInterface::class, function ($container) {
                return PPCHelper::getEnvironment($container->get(Configuration::class), true);
            });
        } catch (Exception $e) {
            Shop::Container()->getLogService()->alert('Can not register service. (' . $e->getMessage() . ')');
        }
    }

    /**
     * @inheritDoc
     * @uses Configuration::getSectionDescription()
     * @uses Configuration::getSectionDescriptionType()
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $tabMapping = Renderer::TAB_MAPPINGS;
        if (isset($tabMapping[$tabName])) {
            try {
                return (new Renderer($this->getPlugin(), $menuID, $smarty))->render($tabMapping[$tabName]);
            } catch (TabNotAvailException) {
                /** @var Plugin $plugin */
                $plugin = $smarty->getTemplateVars('oPlugin');
                if ($plugin !== null) {
                    $plugin->getAdminMenu()->removeItem($menuID);
                }
            }
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }

    /**
     * @return void
     */
    public function checkPaymentNotifications(): void
    {
        $notificationHelper = Notification::getInstance();
        $plugin             = $this->getPlugin();
        $config             = PPCHelper::getConfiguration($plugin);

        if (!$config->getConfigValues()->isAuthConfigured()) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_INFO,
                \__($plugin->getMeta()->getName()),
                \__('Bitte schließen Sie die Konfiguration ab.'),
                Shop::getAdminURL() . '/plugin/' . $plugin->getID()
            );
            $entry->setPluginId($plugin->getPluginID());
            $notificationHelper->addNotify($entry);

            return;
        }

        $this->checkNotificationSandboxMode($notificationHelper);
        $this->checkNotificationWebhook($notificationHelper, $config);
        $this->checkNotificationPendingOrders($notificationHelper, $config);
    }

    /**
     * @param Notification $notificationHelper
     */
    private function checkNotificationSandboxMode(Notification $notificationHelper): void
    {
        $plugin        = $this->getPlugin();
        $paymentActive = false;

        foreach ($plugin->getPaymentMethods()->getMethods() as $paymentMethod) {
            $paypalPayment = Helper::getInstance($plugin)->getPaymentFromID($paymentMethod->getMethodID());
            if ($paypalPayment !== null) {
                $paymentActive = $paymentActive || $paypalPayment->isAssigned();
                $entry         = $paypalPayment->getBackendNotification($plugin);
                if ($entry !== null) {
                    $notificationHelper->addNotify($entry);
                }
            }
        }

        $environment = PPCHelper::getEnvironment();
        if ($paymentActive && $environment->isSandbox()) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($plugin->getMeta()->getName()),
                \__('Zahlung erfolgt im Sandbox Modus.'),
                Shop::getAdminURL() . '/plugin/' . $plugin->getID(),
                \md5($plugin->getID() . '_sandboxMode')
            );
            $entry->setPluginId($plugin->getPluginID());
            $notificationHelper->addNotify($entry);
        }
    }

    /**
     * @param Notification  $notificationHelper
     * @param Configuration $config
     */
    private function checkNotificationWebhook(Notification $notificationHelper, Configuration $config): void
    {
        $plugin         = $this->getPlugin();
        $webhookShopUrl = $config->getWebhookUrl();
        $webhookSeoLink = (new Webhook($plugin, $config))->getWebHookURL();

        if ($webhookShopUrl !== $webhookSeoLink) {
            $webhookTabID = $config->getAdminmenuSettingsId('WEBHOOK');
            $entry        = new NotificationEntry(
                NotificationEntry::TYPE_DANGER,
                \__($plugin->getMeta()->getName()),
                \__('Bitte führen Sie eine Neuregistrierung Ihrer Webhook-URL durch!'),
                Shop::getAdminURL() . '/plugin/' . $plugin->getID() . '#plugin-tab-' . $webhookTabID
            );
            $entry->setPluginId($plugin->getPluginID());
            $notificationHelper->addNotify($entry);
        }
    }

    private function checkNotificationPendingOrders(Notification $notificationHelper, Configuration $config): void
    {
        $plugin        = $this->getPlugin();
        $pendingOrders = new PendingOrders($plugin, $this->getDB());
        $pendingCount  = $pendingOrders->hasPendingOrders();
        if ($pendingCount > 0) {
            $pendingOrdersTabID = $config->getAdminmenuSettingsId('Offene Bestellungen');
            $entry              = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($plugin->getMeta()->getName()),
                \__('Sie haben offene Bestellungen.'),
                Shop::getAdminURL() . '/plugin/' . $plugin->getID() . '#plugin-tab-' . $pendingOrdersTabID,
                md5($plugin->getID() . 'pendingOrders' . $pendingCount)
            );
            $entry->setPluginId($plugin->getPluginID());
            $notificationHelper->addNotify($entry);
        }
    }
    /**
     * @return bool
     */
    private function handleFrontendOnboarding(): bool
    {
        // Attention: this is a frontend link and is therefore executed in frontend context!
        Controller::getInstance($this->getPlugin(), $this->getDB(), $this->getAlert())->run('FinishOnboarding');

        return true;
    }

    /**
     * @param LinkInterface $link
     * @param JTLSmarty     $smarty
     * @return bool
     */
    private function handleFrontendPendingPayment(LinkInterface $link, JTLSmarty $smarty): bool
    {
        parent::prepareFrontend($link, $smarty);

        $plugin  = $this->getPlugin();
        $handler = new PaymentStateHandler($plugin, $this->getDB(), $this->getAlert());
        $handler->checkPaymentState($link, $smarty);
        CheckoutPage::getInstance($plugin)->setPageStep(CheckoutPage::STEP_PENDING);

        return true;
    }

    /**
     * @return bool
     */
    private function handleFrontendWebhook(): bool
    {
        $plugin  = $this->getPlugin();
        $config  = PPCHelper::getConfiguration($plugin);
        $webhook = new WebhookHandler($plugin, $config);
        $webhook->handleCall(
            $config->getWebhookId(),
            \file_get_contents('php://input')
        );

        return true;
    }

    /**
     * @return bool
     */
    private function handleFrontendExpresscheckout(): bool
    {
        $plugin  = $this->getPlugin();
        $config  = PPCHelper::getConfiguration($plugin);
        $handler = new FrontendHandler($plugin, $this->getDB(), $config, $this->getAlert());
        $handler->handleECSOrder();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool
    {
        return match ($link->getTemplate()) {
            'onboarding.tpl'             => $this->handleFrontendOnboarding(),
            'pendingpayment.tpl'         => $this->handleFrontendPendingPayment($link, $smarty),
            'webhook_PayPalCommerce.tpl' => $this->handleFrontendWebhook(),
            'expresscheckout.tpl'        => $this->handleFrontendExpresscheckout(),
            default                      => parent::prepareFrontend($link, $smarty)
        };
    }
}
