<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;

/**
 * Class DeletePendingOrderAll
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class DeletePendingOrderAllController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $alertService  = $this->getAlertService();
        $payments      = $this->getPaymentIds();
        $pendingOrders = new PendingOrders($this->getPlugin(), $this->getDB());
        $orderCount    = 0;
        foreach ($payments as $txnId => $paymentId) {
            if ($pendingOrders->deletePendingPayment((int)$paymentId, $txnId)) {
                $orderCount++;
            }
        }
        $alertService->addSuccess(
            \__('%d Zahlungen gelöscht', $orderCount),
            'pendingPaymentSaved'
        );

        $this->redirectSelf();
    }
}
