<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Product
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class Product extends JSON
{
    /**
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'name'                    => null,
            'vetting_status'          => 'DENIED',
            'active'                  => false,
            'capabilities'            => [],
        ]);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getData()->name ?? null;
    }

    /**
     * @return string
     */
    public function getVettingStatus(): string
    {
        return $this->getData()->vetting_status ?? 'DENIED';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $state = ($this->getData()->status ?? '') === 'ACTIVE';

        return $this->getData()->active ?? $state;
    }

    /**
     * @return string[]
     */
    public function getCapabilities(): array
    {
        return $this->getData()->capabilities ?? [];
    }
}
