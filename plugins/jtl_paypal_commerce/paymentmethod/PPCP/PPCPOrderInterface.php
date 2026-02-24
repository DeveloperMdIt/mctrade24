<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\PPCP;

use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class PPCPOrderInterface
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
interface PPCPOrderInterface
{
    /**
     * @param Order  $createOrder
     * @param string $bnCode
     * @param Logger $logger
     * @return static
     * @throws PPCRequestException | InvalidOrderException
     */
    public static function create(Order $createOrder, string $bnCode, Logger $logger): static;

    /**
     * @param string $orderId
     * @param Logger $logger
     * @return static
     * @throws PPCRequestException | OrderNotFoundException
     */
    public static function load(string $orderId, Logger $logger): static;

    /**
     * @return void
     */
    public function reset(): void;

    /**
     * @param Order  $createOrder
     * @param string $bnCode
     * @return Order
     * @throws PPCRequestException | InvalidOrderException
     */
    public function callCreate(Order $createOrder, string $bnCode): Order;

    /**
     * @param string|null $orderId
     * @param bool        $forceApiCall
     * @return Order
     * @throws PPCRequestException | OrderNotFoundException
     */
    public function callGet(?string $orderId = null, bool $forceApiCall = false): Order;

    /**
     * @param Order $patchOrder
     * @return Order
     * @throws PPCRequestException | OrderNotFoundException
     */
    public function callPatch(Order $patchOrder): Order;

    /**
     * @param string $orderNumber
     * @param string $bnCode
     * @return Order
     * @throws PPCRequestException | OrderNotFoundException
     */
    public function callCapture(string $orderNumber, string $bnCode = MerchantCredentials::BNCODE_CHECKOUT): Order;
}
