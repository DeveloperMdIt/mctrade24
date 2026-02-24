<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Capability
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class Capability extends JSON
{
    /**
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
                'name'  => null,
                'staus' => 'REVOKED',
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
    public function getStatus(): string
    {
        return $this->getData()->status ?? 'REVOKED';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getStatus() === 'ACTIVE';
    }

    /**
     * @return array
     */
    public function getLimits(): array
    {
        return $this->getData()->limits ?? [];
    }

    /**
     * @return bool
     */
    public function hasLimits(): bool
    {
        return count($this->getLimits()) > 0;
    }

    /**
     * @param string $type
     * @return object|null
     */
    public function getLimit(string $type): ?object
    {
        foreach ($this->getLimits() as $limit) {
            if (isset($limit->type) && $limit->type === $type) {
                return $limit;
            }
        }

        return null;
    }
}
