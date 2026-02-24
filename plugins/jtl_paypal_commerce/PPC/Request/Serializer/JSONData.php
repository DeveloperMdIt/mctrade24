<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request\Serializer;

use InvalidArgumentException;

/**
 * Class JSONData
 * @package Plugin\jtl_paypal_commerce\PPC\Request\Serializer
 */
class JSONData extends JSON
{
    /**
     * @param string $varName
     * @return mixed
     */
    public function get(string $varName): mixed
    {
        return $this->data->$varName ?? null;
    }

    /**
     * @param string $varName
     * @return mixed
     */
    public function __get(string $varName): mixed
    {
        return $this->get($varName);
    }

    /**
     * @param string $varName
     * @param mixed  $value
     * @return void
     */
    public function __set(string $varName, mixed $value): void
    {
        throw new InvalidArgumentException('Property is readonly');
    }

    /**
     * @param string $varName
     * @return bool
     */
    public function __isset(string $varName): bool
    {
        return isset($this->data->$varName);
    }
}
