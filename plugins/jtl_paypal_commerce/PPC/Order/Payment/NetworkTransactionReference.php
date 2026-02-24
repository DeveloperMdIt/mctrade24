<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class NetworkTransactionReference
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class NetworkTransactionReference extends JSON
{
    public function setId(string $id): self
    {
        $this->data->id = PPCHelper::validateStr($id, 9, 36);

        return $this;
    }

    public function getId(): string
    {
        return $this->data->id ?? '';
    }

    public function setDate(?string $date): self
    {
        $this->data->date = $date === null ? null : PPCHelper::validateStr($date, 4, 4);

        return $this;
    }

    public function getDate(): string
    {
        return $this->data->date ?? '';
    }

    public function setRefNumber(?string $refNumber): self
    {
        $this->data->acquirer_reference_number = $refNumber === null ? null : PPCHelper::validateStr($refNumber, 1, 36);

        return $this;
    }

    public function getRefNumber(): string
    {
        return $this->data->acquirer_reference_number ?? '';
    }

    public function setNetwork(?string $network): self
    {
        $this->data->network = $network === null ? null : PPCHelper::validateStr($network, 1, 255, '^[A-Z_]+$');

        return $this;
    }

    public function getNetwork(): string
    {
        return $this->data->network ?? '';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->date)) {
            unset($data->date);
        }
        if (empty($data->acquirer_reference_number)) {
            unset($data->acquirer_reference_number);
        }
        if (empty($data->network)) {
            unset($data->network);
        }

        return $data;
    }
}
