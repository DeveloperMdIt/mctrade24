<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class StoredCredential
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class StoredCredential extends JSON
{
    public const PI_CUSTOMER = 'CUSTOMER';
    public const PI_MERCHANT = 'MERCHANT';

    public const PTYPE_ONE_TIME    = 'ONE_TIME';
    public const PTYPE_RECURRING   = 'RECURRING';
    public const PTYPE_UNSCHEDULED = 'UNSCHEDULED';

    public const USAGE_FIRST      = 'FIRST';
    public const USAGE_SUBSEQUENT = 'SUBSEQUENT';
    public const USAGE_DERIVED    = 'DERIVED';

    /**
     * StoredCredential constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @inheritDoc
     */
    public function setData(object|array|string $data): static
    {
        parent::setData($data);

        $nTReference = $this->getData()->previous_network_transaction_reference ?? null;
        if ($nTReference !== null && !($nTReference instanceof NetworkTransactionReference)) {
            $this->setPreviousNetworkTransactionReference(new NetworkTransactionReference($nTReference));
        }

        return $this;
    }

    public function setPaymentInitiator(string $paymentInitiator): self
    {
        if (!\in_array($paymentInitiator, [self::PI_CUSTOMER, self::PI_MERCHANT])) {
            throw new InvalidArgumentException('Invalid payment initiator');
        }

        if ($paymentInitiator !== self::PI_CUSTOMER) {
            if (($this->data->payment_type ?? '') === self::PTYPE_ONE_TIME) {
                throw new InvalidArgumentException('One Time payment requires payment initiator customer');
            }
            if (($this->data->usage ?? '') === self::USAGE_FIRST) {
                throw new InvalidArgumentException('First payment requires payment initiator customer');
            }
        }

        $this->data->payment_initiator = $paymentInitiator;

        return $this;
    }

    public function getPaymentInitiator(): string
    {
        return $this->getData()->payment_initiator ?? self::PI_CUSTOMER;
    }

    public function setPaymentType(string $paymentType): self
    {
        if (!\in_array($paymentType, [self::PTYPE_ONE_TIME, self::PTYPE_RECURRING, self::PTYPE_UNSCHEDULED])) {
            throw new InvalidArgumentException('Invalid payment type');
        }
        if ($paymentType === self::PTYPE_ONE_TIME && $this->getPaymentInitiator() !== self::PI_CUSTOMER) {
            throw new InvalidArgumentException('One Time payment requires payment initiator customer');
        }
        $this->data->payment_type = $paymentType;

        return $this;
    }

    public function getPaymentType(): string
    {
        return $this->getData()->payment_type ?? self::PTYPE_ONE_TIME;
    }

    public function setUsage(string $usage): self
    {
        if (!\in_array($usage, [self::USAGE_DERIVED, self::USAGE_FIRST, self::USAGE_SUBSEQUENT])) {
            throw new InvalidArgumentException('Invalid payment type');
        }
        if ($usage === self::USAGE_FIRST && $this->getPaymentInitiator() !== self::PI_CUSTOMER) {
            throw new InvalidArgumentException('Usage first requires payment initiator customer');
        }

        $this->data->usage = $usage;

        return $this;
    }

    public function getUsage(): string
    {
        return $this->getData()->usage ?? self::USAGE_DERIVED;
    }

    public function setPreviousNetworkTransactionReference(?NetworkTransactionReference $nTReference = null): self
    {
        $this->data->previous_network_transaction_reference = $nTReference;

        return $this;
    }

    public function getPreviousNetworkTransactionReference(): ?NetworkTransactionReference
    {
        return $this->getData()->previous_network_transaction_reference ?? null;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (
            empty($data->previous_network_transaction_reference)
            || (
                $data->previous_network_transaction_reference instanceof SerializerInterface
                && $data->previous_network_transaction_reference->isEmpty()
            )
        ) {
            unset($data->previous_network_transaction_reference);
        }
        if (empty($data->usage)) {
            unset($data->usage);
        }

        return $data;
    }
}
