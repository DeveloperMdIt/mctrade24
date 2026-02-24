<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\PPCP;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use JsonException;
use JTL\Helpers\GeneralObject;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderCaptureRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderCaptureResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderCreateRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderCreateResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderGetRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderGetResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderPatchRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderPatchResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Patch;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchInvoiceId;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchPayer;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchPurchase;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchShippingAddress;
use Plugin\jtl_paypal_commerce\PPC\Order\PatchShippingName;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Order\Shipping;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\Order\TransactionException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\ClientErrorResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class PPCPOrder
 * @package Plugin\jtl_paypal_commerce\paymentmethod\PPCP
 */
class PPCPOrder implements PPCPOrderInterface
{
    /** @var Logger */
    protected Logger $logger;

    /** @var Order|null */
    protected ?Order $order = null;

    /** @var PPCClient */
    protected PPCClient $apiClient;

    /**
     * PPCPOrder constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger    = $logger;
        $this->apiClient = new PPCClient(PPCHelper::getEnvironment(), $logger);
    }

    /**
     * @inheritDoc
     */
    public static function create(Order $createOrder, string $bnCode, Logger $logger): static
    {
        $instance = new static($logger);
        $instance->callCreate($createOrder, $bnCode);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public static function load(string $orderId, Logger $logger): static
    {
        $instance = new static($logger);
        $instance->callGet($orderId, true);

        return $instance;
    }

    /**
     * @param Order  $ppOrder
     * @param string $bnCode
     * @return OrderCreateResponse
     * @throws AuthorizationException | GuzzleException | PPCRequestException
     */
    protected function apiCreateOrder(
        Order $ppOrder,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): OrderCreateResponse {
        $options = [];

        try {
            $options['PayPal-Request-Id'] = Transaction::instance()->getTransactionId(Transaction::CONTEXT_CREATE);
        } catch (TransactionException) {
            unset($options['PayPal-Request-Id']);
        }

        return new OrderCreateResponse($this->apiClient->send(new OrderCreateRequest(
            Token::getInstance()->getToken(),
            $ppOrder,
            $bnCode
        ), $options));
    }

    /**
     * @param string $orderId
     * @return OrderGetResponse
     * @throws AuthorizationException | GuzzleException | PPCRequestException
     */
    protected function apiGetOrder(string $orderId): OrderGetResponse
    {
        return new OrderGetResponse($this->apiClient->send(new OrderGetRequest(
            Token::getInstance()->getToken(),
            $orderId
        )));
    }

    /**
     * @param string  $orderId
     * @param Patch[] $patches
     * @return OrderPatchResponse
     * @throws AuthorizationException | GuzzleException | PPCRequestException
     */
    protected function apiPatchOrder(string $orderId, array $patches): OrderPatchResponse
    {
        return new OrderPatchResponse($this->apiClient->send(new OrderPatchRequest(
            Token::getInstance()->getToken(),
            $orderId,
            $patches
        )));
    }

    /**
     * @param string $orderId
     * @param string $bnCode
     * @return OrderCaptureResponse
     * @throws AuthorizationException | GuzzleException | PPCRequestException
     */
    protected function apiCaptureOrder(string $orderId, string $bnCode): OrderCaptureResponse
    {
        return new OrderCaptureResponse($this->apiClient->send(new OrderCaptureRequest(
            Token::getInstance()->getToken(),
            $orderId,
            $bnCode
        )));
    }

    /**
     * @return Order|null
     */
    protected function getOrder(): ?Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    protected function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->order = null;
    }

    /**
     * @inheritDoc
     */
    public function callCreate(Order $createOrder, string $bnCode): Order
    {
        $this->reset();
        if ($createOrder->getId() !== '') {
            try {
                $ppOrder = $this->callGet($createOrder->getId(), true);
                if (
                    \in_array($ppOrder->getStatus(), [
                        OrderStatus::STATUS_CREATED,
                        OrderStatus::STATUS_APPROVED,
                    ], true)
                ) {
                    return $this->callPatch($createOrder);
                }
            } catch (OrderNotFoundException | PPCRequestException) {
                $this->reset();
            }
        }

        try {
            $ppOrder  = GeneralObject::deepCopy($createOrder);
            $response = $this->apiCreateOrder($createOrder, $bnCode);

            try {
                $this->setOrder($ppOrder->setData($response->getData()));

                return $ppOrder;
            } catch (JsonException $e) {
                $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callCreate: JsonException - ' . $e->getMessage());
                $this->reset();

                throw new InvalidOrderException('PPCPOrder::callCreate: JsonException', $e->getCode(), $e);
            } catch (UnexpectedResponseException $e) {
                throw new PPCRequestException(
                    new ClientErrorResponse($e->getResponse()),
                    $response->getHeader('Paypal-Debug-Id')
                );
            }
        } catch (PPCRequestException $e) {
            $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callCreate: ' . $e->getName(), $e);
            $this->reset();

            throw $e;
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'PPCPOrder::callCreate: OrderResponseFailed - ' . $e->getMessage()
            );
            $this->reset();

            throw new PPCRequestException(new ClientErrorResponse(new Response(500)), []);
        }
    }

    /**
     * @inheritDoc
     */
    public function callGet(?string $orderId = null, bool $forceApiCall = false): Order
    {
        $ppOrder = $this->getOrder();
        if (empty($orderId) && $ppOrder !== null) {
            $orderId = $ppOrder->getId();
        }

        if (empty($orderId)) {
            throw new OrderNotFoundException('no order id is set', 404);
        }

        if (!$forceApiCall && $ppOrder instanceof Order && $orderId === $ppOrder->getId()) {
            return $ppOrder;
        }

        if ($ppOrder === null) {
            $ppOrder = new Order();
        }

        try {
            $response = $this->apiGetOrder($orderId);
            try {
                $this->setOrder($ppOrder->setData($response->getData()));

                return $ppOrder;
            } catch (JsonException $e) {
                $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callGet: JsonException - ' . $e->getMessage());
                $this->reset();

                throw new InvalidOrderException('PPCPOrder::callGet: JsonException', $e->getCode(), $e);
            } catch (UnexpectedResponseException $e) {
                throw new PPCRequestException(
                    new ClientErrorResponse($e->getResponse()),
                    $response->getHeader('Paypal-Debug-Id')
                );
            }
        } catch (PPCRequestException $e) {
            if ($e->getCode() === 404 || $e->getName() === 'RESOURCE_NOT_FOUND') {
                throw new OrderNotFoundException('order does not exists', 404);
            }

            $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callGet: ' . $e->getName(), $e);
            $this->reset();

            throw $e;
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callGet: OrderResponseFailed - ' . $e->getMessage());
            $this->reset();

            throw new PPCRequestException(new ClientErrorResponse(new Response(500)), []);
        }
    }

    private function patchPurchase(PurchaseUnit $purchase, array &$patches): void
    {
        if ($purchase->hasAmount()) {
            $patches[] = new PatchPurchase($purchase);
        }
    }

    private function patchPayer(Payer $payer, Order $ppOrder, array &$patches): void
    {
        if ($ppOrder->getStatus() === OrderStatus::STATUS_CREATED && !$payer->isEmpty()) {
            $patches[] = new PatchPayer($payer, $ppOrder->getPayer() === null ? Patch::OP_ADD : Patch::OP_REPLACE);
        }
    }

    private function patchShipping(Shipping $newShipping, ?Shipping $oldShipping, array &$patches): void
    {
        if (!$newShipping->getAddress()->isEmpty()) {
            $patches[] = new PatchShippingAddress(
                $newShipping->getAddress(),
                $oldShipping === null ? Patch::OP_ADD : Patch::OP_REPLACE
            );
        }
        if (!empty($newShipping->getName())) {
            $patches[] = new PatchShippingName(
                $newShipping->getName(),
                $oldShipping === null || $oldShipping->getName() === null ? Patch::OP_ADD : Patch::OP_REPLACE
            );
        }
    }

    private function patchInvoiceId(string $invoiceId, string $oldInvoiceId, array &$patches): void
    {
        if ($invoiceId !== '') {
            $patches[] = new PatchInvoiceId(
                $invoiceId,
                $oldInvoiceId === '' ? Patch::OP_ADD : Patch::OP_REPLACE
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function callPatch(Order $patchOrder): Order
    {
        $ppOrder  = $this->callGet($patchOrder->getId(), true);
        $purchase = $patchOrder->getPurchase();
        $payer    = $patchOrder->getPayer();
        $shipping = $purchase->getShipping();
        $patches  = [];

        $this->patchPurchase($purchase, $patches);
        if ($payer !== null) {
            $this->patchPayer($payer, $ppOrder, $patches);
        }
        if ($shipping !== null) {
            $this->patchShipping($shipping, $ppOrder->getPurchase()->getShipping(), $patches);
        }
        $this->patchInvoiceId($patchOrder->getInvoiceId(), $ppOrder->getInvoiceId(), $patches);

        try {
            if (!empty($patches)) {
                $this->apiPatchOrder($ppOrder->getId(), $patches);
            }

            return $this->callGet($ppOrder->getId(), true);
        } catch (PPCRequestException $e) {
            $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callPatch: ' . $e->getName(), $e);

            throw $e;
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'PPCPOrder::callPatch: OrderResponseFailed - ' . $e->getMessage()
            );

            throw new PPCRequestException(new ClientErrorResponse(new Response(500)), []);
        }
    }

    /**
     * @inheritDoc
     */
    public function callCapture(string $orderNumber, string $bnCode = MerchantCredentials::BNCODE_CHECKOUT): Order
    {
        $ppOrder = $this->callGet();
        if ($ppOrder->getInvoiceId() !== $orderNumber) {
            $patchOrder = new Order();
            $patchOrder->setId($ppOrder->getId())->getPurchase()->setInvoiceId($orderNumber);
            $ppOrder = $this->callPatch($patchOrder);
        }

        try {
            $response = $this->apiCaptureOrder($ppOrder->getId(), $bnCode);
            $this->setOrder($ppOrder->setData($response->getData()));

            return $ppOrder;
        } catch (PPCRequestException $e) {
            $this->logger->write(\LOGLEVEL_NOTICE, 'PPCPOrder::callCapture: ' . $e->getName(), $e);
            $this->reset();

            throw $e;
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'PPCPOrder::callCapture: OrderResponseFailed - ' . $e->getMessage()
            );
            $this->reset();

            throw new PPCRequestException(new ClientErrorResponse(new Response(500)), []);
        }
    }
}
