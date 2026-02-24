<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCapturePending;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\Repositories\PendingOrdersRepository;

/**
 * Class PendingOrders
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
class PendingOrders
{
    private PluginInterface $plugin;

    private PendingOrdersRepository $repository;

    /**
     * PendingOrders constructor
     * @param PluginInterface              $plugin
     * @param DbInterface                  $db
     * @param PendingOrdersRepository|null $repository
     */
    public function __construct(PluginInterface $plugin, DbInterface $db, ?PendingOrdersRepository $repository = null)
    {
        $this->plugin     = $plugin;
        $this->repository = $repository ?? new PendingOrdersRepository($db, Shop::Container()->getCryptoService());
    }

    public function hasPendingOrders(): int
    {
        return $this->repository->hasPendingOrders($this->plugin->getID(), \defined('PPC_DEBUG') && \PPC_DEBUG);
    }

    public function getPendingOrders(): Collection
    {
        return $this->repository->getPendingOrders($this->plugin->getID(), \defined('PPC_DEBUG') && \PPC_DEBUG);
    }

    public function deletePaymentFromOrder(Order $order, PayPalPaymentInterface $paymentMethod): void
    {
        $txnId = $order->getId();
        if ($order->getPurchase()->getInvoiceId() === null) {
            $order->getPurchase()->setInvoiceId($this->repository->getInvoiceID($txnId));
        }
        $this->repository->deletePaymentFromOrder($txnId);
        $paymentMethod->onPaymentComplete($order);
    }

    public function deletePendingPayment(int $paymentId, string $txnId): bool
    {
        $paymentMethod = Helper::getInstance($this->plugin)->getPaymentFromID($paymentId);
        if ($paymentMethod === null) {
            return false;
        }

        $order = $paymentMethod->getPPOrder($txnId) ?? (new Order())->setId($txnId);
        $this->deletePaymentFromOrder($order, $paymentMethod);

        return true;
    }

    /**
     * @param int                        $paymentId
     * @param string                     $txnId
     * @param AlertServiceInterface|null $as
     * @return bool
     */
    public function applyPendingPayment(int $paymentId, string $txnId, ?AlertServiceInterface $as = null): bool
    {
        $helper        = Helper::getInstance($this->plugin);
        $paymentMethod = $helper->getPaymentFromID($paymentId);
        if ($paymentMethod === null) {
            if ($as !== null) {
                $as->addInfo(
                    \__('Zahlungsart nicht gefunden'),
                    'applyPendingPayment'
                );
            }

            return false;
        }
        $order = (new TCCapturePending())->execute($paymentMethod, $paymentMethod->getPPOrder($txnId));
        if ($order === null) {
            if ($as !== null) {
                $as->addInfo(
                    \__('Zahlung %s nicht vorhanden', $txnId),
                    'applyPendingPayment'
                );
            }

            return false;
        }
        $shopOrder = $helper->getShopOrder($order);
        if ($shopOrder === null) {
            if ($as !== null) {
                $as->addInfo(
                    \__('Bestellung %s nicht gefunden', $order->getInvoiceId()),
                    'applyPendingPayment'
                );
            }

            return false;
        }

        if (
            \in_array($paymentMethod->getValidOrderState($order), [
                OrderStatus::STATUS_COMPLETED,
                OrderStatus::STATUS_DECLINED,
            ], true)
        ) {
            $paymentMethod->handleOrder($order, $shopOrder, true);
        } else {
            if ($as !== null) {
                $as->addInfo(
                    \__('Zahlung %s nicht abgeschlossen', $order->getId()),
                    'applyPendingPayment'
                );
            }

            return false;
        }

        $paymentMethod->onPaymentComplete($order);

        return true;
    }
}
