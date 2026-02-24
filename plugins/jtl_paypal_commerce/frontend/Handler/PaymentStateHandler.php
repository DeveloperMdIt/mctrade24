<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use JTL\Checkout\Bestellung;
use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Link\LinkInterface;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentStateResult;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;

/**
 * Class PaymentStateHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class PaymentStateHandler
{
    private PluginInterface $plugin;
    private DbInterface $db;
    private AlertServiceInterface $alertService;

    /**
     * PaymentStateHandler constructor
     * @param PluginInterface            $plugin
     * @param DbInterface|null           $db
     * @param AlertServiceInterface|null $alertService
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?AlertServiceInterface $alertService = null
    ) {
        $this->plugin       = $plugin;
        $this->db           = $db ?? Shop::Container()->getDB();
        $this->alertService = $alertService ?? Shop::Container()->getAlertService();
    }

    public function getPaymentStateResult(PayPalPaymentInterface $payMethod, bool $timeout): ?PaymentStateResult
    {
        $result = new PaymentStateResult(null, null, null, $timeout);

        try {
            $payMethod->onPendingCapture();
            $ppOrder = $payMethod->getPPOrder();
            if ($ppOrder === null) {
                throw new OrderNotFoundException();
            }
        } catch (OrderNotFoundException) {
            return null;
        }

        switch ($payMethod->getValidOrderState($ppOrder)) {
            case OrderStatus::STATUS_PENDING:
                // Order will be approved... waiting and check again
                $result->setPendingMessage(FrontendHandler::getBackendTranslation('Ihre Zahlung wird überprüft'));
                $result->setCompleteMessage(
                    $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_payment_pending_info')
                );

                break;
            case OrderStatus::STATUS_APPROVED:
                // Order is approved and will be captured... waiting and check again
                $result->setPendingMessage(FrontendHandler::getBackendTranslation(
                    'Ihre Zahlung wurde genehmigt und die Bestellung wird jetzt erfasst'
                ));

                break;
            case OrderStatus::STATUS_COMPLETED:
                // Order is captured and will be finalized... waiting and check again
                $result->setPendingMessage(FrontendHandler::getBackendTranslation(
                    'Ihre Zahlung wurde erfasst und die Bestellung wird jetzt abgeschlossen'
                ));

                break;
            case OrderStatus::STATUS_DECLINED:
                $result->setCompleteMessage(
                    \sprintf(
                        $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_payment_declined'),
                        $ppOrder->getInvoiceId(),
                        $payMethod->getLocalizedPaymentName()
                    )
                );

                break;
            default:
        }
        $result->setRedirect($ppOrder->getLink('paymentRedirect'));
        $result->setState($payMethod->getValidOrderState($ppOrder));
        $payMethod->onPaymentState($result, $timeout);

        return $result;
    }

    public function checkPaymentState(LinkInterface $link, JTLSmarty $smarty): void
    {
        $helper = Helper::getInstance($this->plugin);
        $cUID   = Request::getVar('uid');
        $state  = null;
        if ($cUID !== null) {
            $state     = $this->db->getSingleObject(
                'SELECT tbestellstatus.kBestellung, tbestellung.kZahlungsart, tzahlungsid.txn_id
                    FROM tbestellstatus
                    INNER JOIN tbestellung ON tbestellstatus.kBestellung = tbestellung.kBestellung
                    LEFT JOIN tzahlungsid ON tbestellung.kBestellung = tzahlungsid.kBestellung
                                                 AND tbestellung.kZahlungsart = tzahlungsid.kZahlungsart
                    WHERE tbestellstatus.cUID = :cuid
                    ORDER BY COALESCE(tzahlungsid.dDatum, tbestellstatus.dDatum) DESC',
                ['cuid' => $cUID]
            );
            $payMethod = $state ? $helper->getPaymentFromID((int)$state->kZahlungsart) : null;
        } else {
            $payMethod = $helper->getPaymentFromID(Request::getInt('payment'))
                ?? $helper->getPaymentFromName('PayPalCommerce');
        }
        if ($payMethod === null) {
            Helper::redirectAndExit(Shop::Container()->getLinkService()->getStaticRoute('jtl.php') . '?bestellungen=1');
            exit();
        }

        $ppOrder      = $payMethod->getPPOrder($state->txn_id ?? null);
        $paymentState = $this->getPaymentStateResult($payMethod, Request::hasGPCData('timeout'));
        $shopOrder    = $ppOrder !== null ? $helper->getShopOrder($ppOrder) : null;
        if ($paymentState === null) {
            Helper::redirectAndExit(
                $payMethod->getReturnURL($shopOrder ?? new Bestellung((int)($state->kBestellung ?? 0)))
            );
        }
        if ($paymentState->hasRedirect() && $paymentState->getRedirect() !== $payMethod->getPaymentStateURL($ppOrder)) {
            if ($paymentState->hasCompleteMessage()) {
                $this->alertService->addInfo(
                    $paymentState->getCompleteMessage(),
                    'paymentState'
                );
            }
            Helper::redirectAndExit($paymentState->getRedirect());
        }

        $link->setTitle($payMethod->getLocalizedPaymentName());
        $smarty->assign('waitingBackdrop', !$paymentState->isTimeout())
               ->assign('checkMessage', $paymentState->hasPendingMessage()
                   ? $paymentState->getPendingMessage()
                   : FrontendHandler::getBackendTranslation('Ihre Zahlung wird überprüft'))
               ->assign('methodID', $payMethod->getMethod()->getMethodID())
               ->assign(
                   'orderStateURL',
                   $payMethod->getReturnURL($shopOrder ?? new Bestellung((int)($state->kBestellung ?? 0)))
               );
    }
}
