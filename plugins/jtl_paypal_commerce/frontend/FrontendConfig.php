<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class FrontendConfig
 * @package Plugin\jtl_paypal_commerce\frontend
 */
final class FrontendConfig
{
    /** @var array  */
    private array $config;

    /** @var array */
    private array $expressBuyConfig;

    /** @var bool  */
    private bool $visibility;

    /** @var string  */
    private string $scope;

    /** @var self[] */
    private static array $instance = [];

    /**
     * FrontendConfig constructor
     */
    private function __construct(Configuration $configuration, string $scope)
    {
        $this->scope            = $scope;
        $this->config           = $configuration->mapFrontendSettings(null, null, [
            Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS,
            Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY
        ]);
        $this->expressBuyConfig = $this->config[Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY];
        $this->visibility       = $configuration->checkComponentVisibility($this->expressBuyConfig, $scope);

        self::$instance[$scope] = $this;
    }

    /**
     * @param Configuration $configuration
     * @param string        $scope
     * @return self
     */
    public static function getInstance(Configuration $configuration, string $scope): self
    {
        return self::$instance[$scope] ?? new self($configuration, $scope);
    }

    /**
     * @return bool
     */
    public function checkComponentVisibility(): bool
    {
        return $this->visibility;
    }

    /**
     * @param string|null $scope
     * @return string
     */
    public function getMethod(?string $scope = null): string
    {
        return $this->expressBuyConfig[($scope ?? $this->scope) . '_phpqMethod'];
    }

    /**
     * @param string|null $scope
     * @return string
     */
    public function getSelector(?string $scope = null): string
    {
        return $this->expressBuyConfig[($scope ?? $this->scope) . '_phpqSelector'];
    }

    /**
     * @param string|null $scope
     * @return array
     */
    public function getConfig(?string $scope = null): array
    {
        return $this->config[$scope ?? $this->scope];
    }
}
