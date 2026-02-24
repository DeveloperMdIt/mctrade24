<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;

/**
 * Class DeletePendingOrder
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class DeletePendingOrderController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $paymentId = Request::postInt('paymentId');
        $txnId     = Request::postVar('txnId');
        $alert     = $this->getAlertService();

        if ((new PendingOrders($this->getPlugin(), $this->getDB()))->deletePendingPayment($paymentId, $txnId)) {
            $alert->addSuccess(
                \__('Offene Zahlung gelöscht'),
                'pendingPaymentSaved'
            );
        } else {
            $alert->addError(
                \__('Offene Zahlung konnte nicht gelöscht werden'),
                'pendingPaymentFailed'
            );
        }

        $this->redirectSelf();
    }
}
