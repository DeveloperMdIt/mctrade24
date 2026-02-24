<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use DateTime;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Interface OrderFullResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
interface OrderFullResponse
{
    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getId(): string;

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getStatus(): string;

    /**
     * @return DateTime
     * @throws UnexpectedResponseException
     */
    public function getCreateTime(): DateTime;

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getIntent(): string;

    /**
     * @return Payer|null
     * @throws UnexpectedResponseException
     */
    public function getPayer(): ?Payer;

    /**
     * @return array
     * @throws UnexpectedResponseException
     */
    public function getPurchases(): array;

    /**
     * @param string $referenceId
     * @return PurchaseUnit
     * @throws UnexpectedResponseException
     */
    public function getPurchase(string $referenceId = PurchaseUnit::REFERENCE_DEFAULT): PurchaseUnit;
}
