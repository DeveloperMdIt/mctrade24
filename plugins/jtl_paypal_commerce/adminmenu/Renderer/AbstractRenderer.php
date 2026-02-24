<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class AbstractRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
abstract class AbstractRenderer implements RendererInterface
{
    private PluginInterface $plugin;

    private Configuration $config;

    private Logger $logger;

    private ?AlertServiceInterface $alertService = null;

    private static ?bool $isTabAvailable = null;

    /**
     * AbstractRenderer constructor
     */
    public function __construct(PluginInterface $plugin, ?Configuration $config = null, ?Logger $logger = null)
    {
        $this->plugin = $plugin;
        $this->config = $config ?? PPCHelper::getConfiguration($plugin);
        $this->logger = $logger ?? new Logger(Logger::TYPE_ONBOARDING);
    }

    protected function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    protected function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    protected function getAlert(): AlertServiceInterface
    {
        if ($this->alertService === null) {
            $this->alertService = AlertService::getInstance();
        }

        return $this->alertService;
    }

    /**
     * @throws TabNotAvailException
     */
    public function checkRendering(bool $force = false): void
    {
        if ($force || self::$isTabAvailable === null) {
            $payment = Helper::getInstance($this->plugin)->getPaymentFromName('PayPalCommerce');

            self::$isTabAvailable = $payment !== null && $payment->isValidIntern();
        }

        if (!self::$isTabAvailable) {
            throw new TabNotAvailException(\sprintf(
                \__('Die Zahlungsmethode %s ist nicht verfügbar.'),
                'PayPalCommerce'
            ));
        }
    }
}
