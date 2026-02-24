<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce;

use Illuminate\Support\Collection;
use JTL\Alert\Alert;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;

/**
 * Class AlertService
 * @package Plugin\jtl_paypal_commerce
 */
final class AlertService implements AlertServiceInterface
{
    /** @var AlertServiceInterface */
    private AlertServiceInterface $coreAlert;

    /** @var self */
    private static self $instance;

    /**
     * AlertService constructor
     * @param AlertServiceInterface $coreAlert
     */
    private function __construct(AlertServiceInterface $coreAlert)
    {
        $this->coreAlert = $coreAlert;
        self::$instance  = $this;
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ?? new self(Shop::Container()->getAlertService());
    }

    /**
     * @param array $options
     * @return array
     */
    private function applyDefaultOption(array $options = []): array
    {
        return \array_merge(['saveInSession' => true], $options);
    }

    /**
     * @inheritDoc
     */
    public function initFromSession(): void
    {
        $this->coreAlert->initFromSession();
    }

    /**
     * @inheritDoc
     */
    public function addAlert(string $type, string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addAlert($type, $message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addError(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addError($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addWarning(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addWarning($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addInfo(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addInfo($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addSuccess(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addSuccess($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addDanger(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addDanger($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function addNotice(string $message, string $key, ?array $options = null): ?Alert
    {
        return $this->coreAlert->addNotice($message, $key, $this->applyDefaultOption($options ?? []));
    }

    /**
     * @inheritDoc
     */
    public function getAlert(string $key): ?Alert
    {
        return $this->coreAlert->getAlert($key);
    }

    /**
     * @inheritDoc
     */
    public function getAlertlist(): Collection
    {
        return $this->coreAlert->getAlertlist();
    }

    /**
     * @inheritDoc
     */
    public function alertTypeExists(string $type): bool
    {
        return $this->coreAlert->alertTypeExists($type);
    }

    /**
     * @inheritDoc
     */
    public function displayAlertByKey(string $key): void
    {
        $this->coreAlert->displayAlertByKey($key);
    }

    /**
     * @inheritDoc
     */
    public function removeAlertByKey(string $key): void
    {
        $this->coreAlert->removeAlertByKey($key);
    }
}
