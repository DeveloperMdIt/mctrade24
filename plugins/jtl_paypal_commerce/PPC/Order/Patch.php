<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Patch
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Patch extends JSON
{
    public const OP_ADD     = 'add';
    public const OP_REMOVE  = 'remove';
    public const OP_REPLACE = 'replace';

    /**
     * Patch constructor.
     * @param string $path
     * @param JSON   $value
     * @param string $op
     */
    public function __construct(string $path, JSON $value, string $op = self::OP_REPLACE)
    {
        parent::__construct((object)[]);

        $this->setOp($op);
        $this->setPath($path);
        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getOp(): string
    {
        return $this->data->op;
    }

    /**
     * @param string $op
     * @return Patch
     */
    public function setOp(string $op): self
    {
        if (!\in_array($op, [self::OP_REPLACE, self::OP_ADD, self::OP_REMOVE])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid op type.', $op));
        }

        $this->data->op = $op;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->data->path;
    }

    /**
     * @param string $path
     * @return Patch
     */
    public function setPath(string $path): self
    {
        $this->data->path = $path;

        return $this;
    }

    /**
     * @return JSON
     */
    public function getValue(): JSON
    {
        return $this->data->value;
    }

    /**
     * @param JSON $value
     * @return Patch
     */
    public function setValue(JSON $value): self
    {
        $this->data->value = $value;

        return $this;
    }
}
