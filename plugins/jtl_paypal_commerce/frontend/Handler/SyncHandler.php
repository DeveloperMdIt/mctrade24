<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\PPCPOrder;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

use function Functional\first;

/**
 * Class SyncHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class SyncHandler
{
    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var DbInterface */
    private DbInterface $db;

    /** @var PendingOrders */
    private PendingOrders $pendingOrders;

    /** @var Helper */
    private Helper $helper;

    /**
     * syncHandler constructor
     */
    public function __construct(PluginInterface $plugin, ?DbInterface $db = null)
    {
        $this->plugin        = $plugin;
        $this->db            = $db ?? Shop::Container()->getDB();
        $this->pendingOrders = new PendingOrders($this->plugin, $this->db);
        $this->helper        = Helper::getInstance($this->plugin);
    }

    protected function handleOrder(object $orderItem, PayPalPaymentInterface $method): void
    {
        $logger = $method->getLogger();
        try {
            $ppcp  = PPCPOrder::load($orderItem->txn_id, $logger);
            $order = $ppcp->callGet($orderItem->txn_id);
            $state = $method->getValidOrderState($order);
        } catch (PPCRequestException $e) {
            $logger->write(\LOGLEVEL_ERROR, 'Order GET-Request failed: ' . $e->getMessage());

            return;
        } catch (OrderNotFoundException) {
            $logger->write(\LOGLEVEL_DEBUG, 'Payment with txn_id ' . $orderItem->txn_id . ' - set as unpayed');
            $order = (new Order())->setId($orderItem->txn_id);
            $this->pendingOrders->deletePaymentFromOrder($order, $method);

            return;
        }

        if (\in_array($state, [OrderStatus::STATUS_COMPLETED, OrderStatus::STATUS_DECLINED], true)) {
            $shopOrder = $this->helper->getShopOrder($order);
            if ($shopOrder !== null) {
                $logger->write(\LOGLEVEL_DEBUG, 'Order with txn_id ' . $orderItem->txn_id . ' - will be handled');
                $method->handleOrder($order, $shopOrder, true);
            } else {
                $logger->write(\LOGLEVEL_DEBUG, 'For Payment with txn_id ' . $orderItem->txn_id . ' - exists no order');
            }
        }
    }

    public function lastJobs(array &$args): void
    {
        $pluginId = $this->plugin->getID();
        $job      = first($args['jobs'], function (object $item) use ($pluginId) {
            return (int)$item->nJob === $pluginId;
        });
        if ($job === null) {
            return;
        }

        $orderItems = $this->pendingOrders->getPendingOrders();
        if ($orderItems->isEmpty() !== true) {
            foreach ($orderItems as $orderItem) {
                $method = $this->helper->getPaymentFromID((int)$orderItem->kZahlungsart);
                if ($method === null) {
                    continue;
                }

                $this->handleOrder($orderItem, $method);
            }
            $args['jobs'] = [];
        }
        $this->db->update('tlastjob', 'kJob', (int)$job->kJob, (object)[
            'dErstellt' => \date('Y-m-d H:i:s'),
        ]);
    }
}
