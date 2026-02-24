<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Exception;
use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Backend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\adminmenu\Controller\ControllerInterface;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class Controller
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
final class Controller
{
    /** @var static[] */
    private static array $instance = [];

    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var DbInterface */
    private DbInterface $db;

    /** @var Configuration */
    private Configuration $config;

    /** @var Logger */
    private Logger $logger;

    /** @var AlertServiceInterface */
    private AlertServiceInterface $alertService;

    /**
     * Controller constructor.
     * @param PluginInterface       $plugin
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     */
    protected function __construct(PluginInterface $plugin, DbInterface $db, AlertServiceInterface $alertService)
    {
        $this->plugin       = $plugin;
        $this->db           = $db;
        $this->config       = PPCHelper::getConfiguration($plugin);
        $this->logger       = new Logger(Logger::TYPE_ONBOARDING);
        $this->alertService = $alertService;

        self::$instance[$plugin->getPluginID()] = $this;
    }

    /**
     * @param PluginInterface            $plugin
     * @param DbInterface|null           $db
     * @param AlertServiceInterface|null $alertService
     * @return self
     */
    public static function getInstance(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?AlertServiceInterface $alertService = null
    ): self {
        return self::$instance[$plugin->getPluginID()] ?? new self(
            $plugin,
            $db ?? Shop::Container()->getDB(),
            $alertService ?? AlertService::getInstance()
        );
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     * @noinspection PhpUnused
     */
    public function getStatic(string $name, mixed $default = null): mixed
    {
        $statics = Backend::get('static.' . $this->plugin->getPluginID(), []);

        return $statics[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @noinspection PhpUnused
     */
    public function setStatic(string $name, mixed $value): void
    {
        $statics        = Backend::get('static.' . $this->plugin->getPluginID(), []);
        $statics[$name] = $value;
        Backend::set('static.' . $this->plugin->getPluginID(), $statics);
    }

    /**
     * @param string $name
     * @noinspection PhpUnused
     */
    public function clearStatic(string $name): void
    {
        $statics = Backend::get('static.' . $this->plugin->getPluginID(), []);
        unset($statics[$name]);
        Backend::set('static.' . $this->plugin->getPluginID(), $statics);
    }

    /**
     * @param string $task
     * @uses SaveCredentialsManuallyController
     * @uses SaveSettingsController
     * @uses ResetCredentialsController
     * @uses CheckPaymentController
     * @uses ChangeWorkingModeController
     * @uses FinishOnboardingController
     * @uses CreateWebhookController
     * @uses RefreshWebhookController
     * @uses ResetSettingsController
     * @uses DeleteWebhookController
     * @uses ChangeShipmentTrackingController
     * @uses SaveCarrierMappingController
     * @uses DeleteCarrierMappingController
     * @uses DeletePendingOrderController
     * @uses DeletePendingOrderAllController
     * @uses ApplyPendingOrderController
     * @uses ApplyPendingOrderAllController
     */
    public function run(string $task): void
    {
        $className = 'Plugin\\jtl_paypal_commerce\\adminmenu\Controller\\' . \ucfirst($task) . 'Controller';
        if (\class_exists($className) && is_a($className, ControllerInterface::class, true)) {
            $controller = new $className($this->plugin, $this->db, $this->config, $this->logger, $this->alertService);
            try {
                $controller->run();
            } catch (Exception $e) {
                $this->alertService->addError($e->getMessage(), 'controllerError');
            }
        }
    }
}
