<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\adminmenu\PendingOrders;

/**
 * Class ApplyPendingOrderController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ApplyPendingOrderController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $paymentId = Request::postInt('paymentId');
        $txnId     = (string)Request::postVar('txnId');
        $alert     = $this->getAlertService();

        if (
            (new PendingOrders($this->getPlugin(), $this->getDB()))
                ->applyPendingPayment($paymentId, $txnId, $alert)
        ) {
            $alert->removeAlertByKey('paymentInformation');
            // Workaround - removeAlertByKey does not remove alert from session
            unset($_SESSION['alerts']['paymentInformation']);
            $alert->addSuccess(
                \__('Offene Zahlung zugeordnet'),
                'pendingPaymentSaved'
            );
        } else {
            $alert->addError(
                \__('Offene Zahlung konnte nicht zugeordnet werden'),
                'pendingPaymentFailed'
            );
        }

        $this->redirectSelf();
    }
}
