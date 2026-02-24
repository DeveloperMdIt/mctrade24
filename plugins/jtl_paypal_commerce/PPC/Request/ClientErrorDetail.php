<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ClientErrorDetail
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class ClientErrorDetail extends JSON
{
    /**
     * ClientErrorDetail constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->getData()->location ?? null;
    }

    /**
     * @return string|null
     */
    public function getIssue(): ?string
    {
        return $this->getData()->issue ?? null;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData()->description ?? null;
    }

    /**
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->getData()->field ?? null;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->getData()->value ?? null;
    }
}
