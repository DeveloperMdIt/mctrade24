<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;

/**
 * Class ApplyPendingOrderAllController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ApplyPendingOrderAllController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $payments      = $this->getPaymentIds();
        $pendingOrders = new PendingOrders($this->getPlugin(), $this->getDB());
        $orderCount    = 0;
        $alertService  = $this->getAlertService();
        foreach ($payments as $txnId => $paymentId) {
            if ($pendingOrders->applyPendingPayment((int)$paymentId, (string)$txnId, $alertService)) {
                $orderCount++;
            }
        }
        $alertService->removeAlertByKey('paymentInformation');
        // Workaround - removeAlertByKey does not remove alert from session
        unset($_SESSION['alerts']['paymentInformation']);
        $alertService->addSuccess(
            \__('%d Zahlungen zugeordnet', $orderCount),
            'pendingPaymentSaved'
        );

        $this->redirectSelf();
    }
}
