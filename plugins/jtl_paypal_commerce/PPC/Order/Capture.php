<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use DateTime;
use Exception;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Capture
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Capture extends JSON
{
    /**
     * Capture constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->getData()->id ?? null;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getData()->status ?? OrderStatus::STATUS_UNKONWN;
    }

    /**
     * @return AmountWithBreakdown
     */
    public function getAmount(): AmountWithBreakdown
    {
        $amount = new AmountWithBreakdown($this->getData()->amount);
        foreach ($this->getData()->seller_receivable_breakdown ?? [] as $key => $value) {
            $amount->addBreakdownItem($key, new Amount($value));
        }

        return $amount;
    }

    /**
     * @return string|null
     */
    public function getInvoiceId(): ?string
    {
        return $this->getData()->invoice_id ?? null;
    }

    /**
     * @return string|null
     */
    public function getCustomId(): ?string
    {
        return $this->getData()->custom_id ?? null;
    }

    /**
     * @return object
     */
    public function getSupplementaryData(): object
    {
        $data = $this->getData()->supplementary_data ?? (object)['related_ids' => null];
        if (!isset($data->related_ids)) {
            $data->related_ids = (object)['order_id' => ''];
        } elseif (!isset($data->related_ids->order_id)) {
            $data->related_ids->order_id = '';
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getRelatedOrderId(): string
    {
        return $this->getSupplementaryData()->related_ids->order_id;
    }

    /**
     * @return DateTime
     */
    public function getCreateTime(): DateTime
    {
        try {
            return new DateTime($this->getData()->create_time);
        } catch (Exception) {
            return new DateTime();
        }
    }

    /**
     * @return DateTime
     */
    public function getUpdateTime(): DateTime
    {
        try {
            return new DateTime($this->getData()->update_time);
        } catch (Exception) {
            return new DateTime();
        }
    }
}
