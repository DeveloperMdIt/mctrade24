<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class AbstractController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
abstract class AbstractController implements ControllerInterface
{
    private PluginInterface $plugin;

    private DbInterface $db;

    private Configuration $config;

    private AlertServiceInterface $alertService;

    private Logger $logger;

    /**
     * AbstractController constructor
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?Configuration $config = null,
        ?Logger $logger = null,
        ?AlertServiceInterface $alertService = null,
    ) {
        $this->plugin       = $plugin;
        $this->db           = $db ?? Shop::Container()->getDB();
        $this->config       = $config ?? PPCHelper::getConfiguration($plugin);
        $this->alertService = $alertService ?? AlertService::getInstance();
        $this->logger       = $logger ?? new Logger(Logger::TYPE_ONBOARDING);
    }

    /**
     * @noinspection PhpNoReturnAttributeCanBeAddedInspection
     */
    protected function redirect(array $params = [], string $file = 'plugin'): void
    {
        \header(
            'Location: ' . Shop::getURL() . '/' . \PFAD_ADMIN . $file . '?' . \implode('&', $params),
            true,
            302
        );
        exit();
    }

    protected function redirectSelf(array $params = []): void
    {
        $params = \array_merge([
            'kPluginAdminMenu=' . Request::postInt('kPluginAdminMenu'),
            'panelActive=' . Request::postInt('panelActive'),
        ], $params);

        $this->redirect($params, 'plugin/' . $this->getPlugin()->getID());
    }

    protected function getPaymentIds(): array
    {
        $payments = Request::postVar('paymentIds', []);
        if (\count($payments) === 0) {
            $this->getAlertService()->addWarning(\__('Keine Zahlung ausgewählt'), 'pendingPaymentFailed');
            $this->redirectSelf();
        }

        return $payments;
    }

    protected function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    protected function getDB(): DbInterface
    {
        return $this->db;
    }

    protected function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    protected function getAlertService(): AlertServiceInterface
    {
        return $this->alertService;
    }
}
