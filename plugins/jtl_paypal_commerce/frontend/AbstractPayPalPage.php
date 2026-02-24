<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Plugin\PluginInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class AbstractPayPalPage
 * @package Plugin\jtl_paypal_commerce\frontend
 */
abstract class AbstractPayPalPage
{
    /** @var int|null */
    protected ?int $pageStep = null;

    protected PluginInterface $plugin;

    protected Configuration $config;

    /** @var static[] */
    protected static array $instance = [];

    /**
     * AbstractPayPalPage constructor.
     * @param PluginInterface    $plugin
     * @param Configuration|null $config
     */
    private function __construct(PluginInterface $plugin, ?Configuration $config = null)
    {
        $this->plugin = $plugin;
        $this->config = $config ?? PPCHelper::getConfiguration($plugin);

        static::$instance[static::class][$plugin->getPluginID()] = $this;
    }

    public static function getInstance(PluginInterface $plugin): static
    {
        return static::$instance[static::class][$plugin->getPluginID()] ?? new static(
            $plugin,
            PPCHelper::getConfiguration($plugin)
        );
    }

    public static function getInstanceInitialized(PluginInterface $plugin): ?static
    {
        return static::$instance[static::class][$plugin->getPluginID()] ?? null;
    }

    public function hasValidStep(): bool
    {
        return $this->pageStep !== null;
    }

    /**
     * @return int
     */
    public function getPageStep(): int
    {
        return $this->pageStep ?? 0;
    }

    /**
     * @param int $pageStep
     */
    public function setPageStep(int $pageStep): void
    {
        $this->pageStep = $pageStep;
    }

    abstract public function render(JTLSmarty $smarty): void;
}
