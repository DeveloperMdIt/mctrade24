<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5;

use Exception;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Rechnungsadresse;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Customer\Customer;
use Plugin\s360_amazonpay_shop5\lib\Controllers\Admin\AdminAjaxController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\Admin\AdminConfigController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\Admin\AdminOrdersController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\Admin\AdminSubscriptionsController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\AjaxController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\CronjobController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\CustomerAccountController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\FrontendOutputController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\LoginController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SyncController;
use Plugin\s360_amazonpay_shop5\lib\Cron\Cronjob;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Mappers\AddressMapper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Events;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Utils\SmartyHelper;
use Smarty;
use SmartyException;
use Throwable;

/**
 * Class Bootstrap
 *
 * Bootstrap class.
 *
 * Note: We have to extend the Bootstrapper class, not implement the BootstrapperInterface.
 *
 * @package Plugin\s360_amazonpay_shop5
 */
class Bootstrap extends Bootstrapper {

    use JtlLoggerTrait;

    protected const ADMIN_TAB_NAME_ACCOUNT = 'lpaTabAccount';
    protected const ADMIN_TAB_NAME_CONFIG = 'lpaTabConfig';
    protected const ADMIN_TAB_NAME_ORDERS = 'lpaTabOrders';
    protected const ADMIN_TAB_NAME_VOICECOMMERCE = 'lpaTabVoiceCommerce';
    protected const ADMIN_TAB_NAME_SUBSCRIPTION_OVERVIEW = 'lpaTabSubscriptionOverview';
    protected const ADMIN_TAB_NAME_SUBSCRIPTION_CONFIG = 'lpaTabSubscriptionConfig';

    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher): void {

        // let the parent do its stuff
        parent::boot($dispatcher);

        /**
         * Hook registration / handling.
         */
        $dispatcher->listen('shop.hook.' . \HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN, function (array $args) {
            // Hook into deletion of a customer, e.g. to remove mappings from our tables.
            try {
                $jtlCustomerId = isset($_SESSION['Kunde']) ? (int)$_SESSION['Kunde']->kKunde : 0;
                if (!empty($jtlCustomerId)) {
                    $controller = new CustomerAccountController();
                    $controller->handleAccountDeletion($jtlCustomerId);
                }
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_JTL_PAGE_KUNDENACCOUNTLOESCHEN);
            }
        });
        $dispatcher->listen('shop.hook.' . \HOOK_JTL_PAGE_REDIRECT, function (array $args) {
            try {
                $controller = new LoginController($this->getPlugin());
                $controller->handleRedirectAfterLogin();
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_JTL_PAGE_REDIRECT);
            }
        });
        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function (array $args) {
            // Hook into template output. E.g. to embed our buttons.
            try {
                $controller = new FrontendOutputController($this->getPlugin());
                $controller->handleSmartyOutput();
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_SMARTY_OUTPUTFILTER);
            }
        });
        $dispatcher->listen('shop.hook.' . \HOOK_BESTELLUNGEN_XML_BEARBEITESET, function (array $args) {
            // Hook into order syncs. This is done per order. E.g. to detect shipment of orders and trigger captures.
            try {
                $orderBefore = $args['oBestellung'];
                // reload order from the database, this is the order after the sync.
                $orderAfter = new Bestellung($orderBefore->kBestellung);
                $controller = new SyncController($this->getPlugin());
                $controller->handleOrderUpdate($orderBefore, $orderAfter);
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_BESTELLUNGEN_XML_BEARBEITESET);
            }
        });
        // Special fallback / override function that can detect charges that should be captured because the respective order is in a delivered state, but are not.
        // It hooks into the last jobs on wawi sync and *into every wawi sync*, therefore enabling this *may be* costly and slow down the sync.
        if(defined(Constants::DESYNC_ORDER_CHECK_CONSTANT) && constant(Constants::DESYNC_ORDER_CHECK_CONSTANT) === true){
            $dispatcher->listen('shop.hook.' . \HOOK_LASTJOBS_HOLEJOBS, function (array $args) {
                // Hook into last job on wawi sync to check for desynced orders.
                try {
                    $controller = new SyncController($this->getPlugin());
                    $controller->handleDesyncedOrders();
                } catch (Throwable $e) {
                    $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_LASTJOBS_HOLEJOBS);
                }
            });
        }
        $dispatcher->listen('shop.hook.' . \HOOK_IO_HANDLE_REQUEST, function (array $args) {
            // Hook into ajax request handling. E.g. to do/capture our own Ajax requests.
            try {
                $controller = new AjaxController($args['io'], $args['request'], $this->getPlugin());
                $controller->handle();
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_IO_HANDLE_REQUEST);
            }
        });
        $dispatcher->listen('shop.hook.' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE, function (array &$args) {
            try {
                if (Shop::has('lpaBillingAddressOverride')) {
                    // Use Hook 74 to manipulate the billing address BEFORE it is saved to the database.
                    $amazonBillingAddress = Shop::get('lpaBillingAddressOverride');
                    if ($amazonBillingAddress instanceof Rechnungsadresse) {
                        $this->debugLog('Overriding billing address with billing address from Amazon ...');
                        $args['billingAddress'] = AddressMapper::overrideBillingAddressWithAmazonPayData($args['billingAddress'], $amazonBillingAddress);
                    }
                }
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE);
            }
        });
        $dispatcher->listen('shop.hook.' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, function (array $args) {
            try {
                if (Shop::has('lpaForceOrderPending')) {
                    $args['oBestellung']->cAbgeholt = 'Y';
                }
            } catch (Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB);
            }
        });
        // Hook cron job - this is either done as "real" cronjob or as hook in lastjobs on wawi sync
        if (Config::getInstance()->getCronMode() === Config::CRON_MODE_TASK) {
            $dispatcher->listen(Event::MAP_CRONJOB_TYPE, function (array &$args) {
                if ($args['type'] === Constants::CRON_JOB_TYPE_SYNC) {
                    $args['mapping'] = Cronjob::class;
                }
            });
        } else {
            if (Config::getInstance()->getCronMode() === Config::CRON_MODE_SYNC) {
                $dispatcher->listen('shop.hook.' . \HOOK_LASTJOBS_HOLEJOBS, function (array $args) {
                    // Hook into last job on wawi sync.
                    try {
                        $controller = new CronjobController();
                        $controller->run();
                    } catch (Exception $e) {
                        $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Hook ' . \HOOK_LASTJOBS_HOLEJOBS);
                    }
                });
            }
        }

        // Listen to our own events, e.g. to send mails
        $dispatcher->listen(Events::AFTER_SUBSCRIPTION_IN_REVIEW, function(array $args) {
            try {
                if($args['reason'] === Subscription::REASON_MERCHANT_PAUSED) {
                    return; // The merchant changed the status themselves, no need for an info mail.
                }
                // Send warning mail to merchant with $args['subscription'] and $args['reason']
                /** @var Subscription $subscription */
                $subscription = $args['subscription'];
                $order = new Bestellung($subscription->getShopOrderId());

                $data = new \stdClass();
                $data->cBestellNr = $order->kBestellung > 0 ? $order->cBestellNr : '-';
                $data->chargePermissionId = $subscription->getChargePermissionId();
                $data->message = 'Subscription "' . $subscription->getId() . '" Review: ' . $args['reason'];

                $mailer = Shop::Container()->get(Mailer::class);
                $mail = new Mail();
                $mailTemplate = $mail->createFromTemplateID(
                    'kPlugin_' . $this->getPlugin()->getID() . '_amazonpayinfo',
                    $data
                );
                $notificationMailAddress = Config::getInstance()->getSubscriptionNotificationMailAddress();
                if(empty($notificationMailAddress)) {
                    $notificationMailAddress = $config['emails']['email_master_absender'] ?? '';
                }
                $mail->setToMail($notificationMailAddress);
                $mail->setToName($mailer->getConfig()['emails']['email_master_absender_name']);
                $mailer->send($mailTemplate);
            } catch(Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Event ' . Events::AFTER_SUBSCRIPTION_IN_REVIEW);
            }
        });
        $dispatcher->listen(Events::AFTER_SUBSCRIPTION_CREATED, function(array $args) {
            try {
                // Send new subscription mail.
                $data = new \stdClass();
                $data->tkunde = $args['customer'];
                $data->order = $args['order'];
                $data->amazonPaySubscription = $args['subscription'];
                $mailer = Shop::Container()->get(Mailer::class);
                $mail = new Mail();
                $mailer->send($mail->createFromTemplateID('kPlugin_' . $this->getPlugin()->getID() . '_' . Constants::MAIL_TEMPLATE_SUBSCRIPTION_STARTED, $data));
            } catch(Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Event ' . Events::AFTER_SUBSCRIPTION_CREATED);
            }
        });
        $dispatcher->listen(Events::AFTER_SUBSCRIPTION_CANCELED, function(array $args) {
            try {
                // Send mail to customer informing them about the ended subscription, unless they deleted their account
                $reason = $args['reason'];
                if($reason !== Subscription::REASON_ACCOUNT_DELETED) {
                    $subscription = $args['subscription'];
                    /** @var Subscription $subscription */
                    $customer = new Customer($subscription->getJtlCustomerId());
                    if((int) $customer->kKunde > 0) {
                        $data = new \stdClass();
                        $data->tkunde = $customer;
                        $data->amazonPaySubscription = $args['subscription'];
                        $mailer = Shop::Container()->get(Mailer::class);
                        $mail = new Mail();
                        $mailer->send($mail->createFromTemplateID('kPlugin_' . $this->getPlugin()->getID() . '_' . Constants::MAIL_TEMPLATE_SUBSCRIPTION_STOPPED, $data));
                    }
                }
            } catch(Throwable $e) {
                $this->errorLog($e->getCode() . ': ' . $e->getMessage(), 'Exception in Event ' . Events::AFTER_SUBSCRIPTION_CANCELED);
            }
        });

        /*
         * JTL-Shop 5.4.0 requires explicit registration of used classes and functions
         */
        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_INC, function (array $args) {
            SmartyHelper::registerSmartyFunctions($args['smarty'] ?? Shop::Smarty()); // Needed for JTL-Shop 5.4.0 / Smarty 4.5
        });

        // Set our own plugin instance for other classes to use without re-initializing the plugin instance
        Plugin::setInstance($this->getPlugin());
    }

    /**
     * This is called on plugin updates. Makes you wonder what kind of information newVersion carries.
     * Isn't newVersion always this current version? Yes, it is.
     *
     * For now, this does nothing.
     *
     * @param mixed $oldVersion
     * @param mixed $newVersion
     * @return mixed
     */
    public function updated($oldVersion, $newVersion) {
        parent::updated($oldVersion, $newVersion);

        // Fallthrough pattern with switch (notice the missing break-statements!):
        switch ($oldVersion) {
            case '0.1.0':
                // do stuff for 0.1.0 to 0.2.0
            case '0.2.0':
                // do stuff to this current version, etc.pp.
        }

        // Bug Workaround - JTL-Shop 5.0.0 does not clear cache when a plugin gets installed via the extension store
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE,
            CACHING_GROUP_LICENSES, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX
        ]);
    }

    public function installed() {
        parent::installed();

        // We have to check if we have a previous config with enabled cron job and re-enable it in this case
        if(Config::getInstance()->getCronMode() === Config::CRON_MODE_TASK) {
            Config::getInstance()->enableCronjob();
        }

        // Bug Workaround - JTL-Shop 5.0.0 does not clear cache when a plugin gets installed via the extension store
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE, CACHING_GROUP_LANGUAGE,
            CACHING_GROUP_LICENSES, CACHING_GROUP_PLUGIN, CACHING_GROUP_BOX
        ]);
    }

    /**
     * @inheritDoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string {
        try {
            SmartyHelper::registerSmartyFunctions($smarty); // Needed for JTL-Shop 5.4.0 / Smarty 4.5
            switch ($tabName) {
                case self::ADMIN_TAB_NAME_ORDERS:
                    // check for ajax requests in one adminmenu tab/link only, might as well do it here
                    if (isset($_GET['isLpaAjax']) && (int)$_GET['isLpaAjax'] === 1) {
                        try {
                            $controller = new AdminAjaxController($this->getPlugin());
                            echo json_encode($controller->handleAjax(), JSON_THROW_ON_ERROR);
                            exit();
                        } catch (Exception $ex) {
                            echo json_encode([
                                'result' => 'error',
                                'messages' => [$ex->getMessage()]
                            ]);
                            exit();
                        }
                    }
                    $controller = new AdminOrdersController($this->getPlugin());
                    return $controller->handle();
                case self::ADMIN_TAB_NAME_ACCOUNT:
                    $controller = new AdminConfigController($this->getPlugin());
                    return $controller->handleAccount();
                case self::ADMIN_TAB_NAME_CONFIG:
                    $controller = new AdminConfigController($this->getPlugin());
                    return $controller->handleConfig();
                case self::ADMIN_TAB_NAME_VOICECOMMERCE:
                    return $smarty->fetch($this->getPlugin()->getPaths()->getAdminPath() . '/template/voicecommerce.tpl');
                case self::ADMIN_TAB_NAME_SUBSCRIPTION_OVERVIEW:
                    $controller = new AdminSubscriptionsController($this->getPlugin());
                    return $controller->handle();
                case self::ADMIN_TAB_NAME_SUBSCRIPTION_CONFIG:
                    $controller = new AdminConfigController($this->getPlugin());
                    return $controller->handleSubscription();
                default:
                    return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

}