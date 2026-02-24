<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Illuminate\Support\Collection;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Bestellung;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class AbstractPaymentFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
abstract class AbstractPaymentFrontend implements PaymentFrontendInterface
{
    /** @var PluginInterface */
    protected PluginInterface $plugin;

    /** @var PayPalPaymentInterface */
    protected PayPalPaymentInterface $paymentMethod;

    /** @var JTLSmarty */
    protected JTLSmarty $smarty;

    /** @var PayPalFrontend */
    protected PayPalFrontend $frontend;

    /** @var AlertServiceInterface|null */
    private ?AlertServiceInterface $alertService = null;

    /**
     * AbstractPaymentFrontend constructor
     * @param PluginInterface        $plugin
     * @param PayPalPaymentInterface $paymentMethod
     * @param JTLSmarty              $smarty
     */
    public function __construct(PluginInterface $plugin, PayPalPaymentInterface $paymentMethod, JTLSmarty $smarty)
    {
        $this->plugin        = $plugin;
        $this->paymentMethod = $paymentMethod;
        $this->smarty        = $smarty;
        $this->frontend      = new PayPalFrontend($plugin, PPCHelper::getConfiguration($plugin), $smarty);
    }

    /**
     * @return AlertServiceInterface
     */
    protected function getAlert(): AlertServiceInterface
    {
        if ($this->alertService === null) {
            $this->alertService = AlertService::getInstance();
        }

        return $this->alertService;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod(): PayPalPaymentInterface
    {
        return $this->paymentMethod;
    }

    /**
     * @inheritDoc
     */
    public function getPayPalFrontend(): PayPalFrontend
    {
        return $this->frontend;
    }

    public function renderAccountPage(): void
    {
        // todo: placeholder for SHOP-8157
    }

    /**
     * @inheritDoc
     */
    public function renderOrderDetailPage(Bestellung $shopOrder, PayPalPaymentInterface $method): void
    {
        /** @var Collection $incommingPayments */
        $incommingPayments = $this->smarty->getTemplateVars('incommingPayments');
        $assignedPayments  = $method->getAssignedPayments($shopOrder);
        foreach ($assignedPayments as $aP) {
            $capture = $aP->getCapture();
            if (
                $aP->hasIncommingPayment() || !\in_array($capture->getStatus(), [
                    OrderStatus::STATUS_PENDING,
                    OrderStatus::STATUS_PENDING_APPROVAL,
                    OrderStatus::STATUS_DECLINED
                ], true)
            ) {
                /** what to do if state is completed and no incomming payment exists? */
                continue;
            }

            $paymentName = $method->getLocalizedPaymentName();
            $item        = (object)[
                'kZahlungseingang'    => null,
                'cZahlungsanbieter'   => $paymentName,
                'fBetrag'             => $capture->getAmount()->getValue(),
                'cISO'                => $capture->getAmount()->getCurrencyCode(),
                'dZeit'               => $capture->getUpdateTime()->format('Y-m-d H:i:s'),
                'paymentLocalization' => Preise::getLocalizedPriceWithoutFactor(
                    $capture->getAmount()->getValue(),
                    Currency::fromISO($capture->getAmount()->getCurrencyCode())
                )
                . ' ' . FrontendHandler::getBackendTranslation($capture->getStatus() . ' on') . ' '
                . $capture->getUpdateTime()->format('d.m.Y'),
            ];
            if ($incommingPayments->has($paymentName)) {
                /** @var Collection $group */
                $group = $incommingPayments->get($paymentName);
                $group->add($item);
            } else {
                $incommingPayments->put($paymentName, new Collection([$item]));
            }
        }

        $this->smarty->assign('incommingPayments', $incommingPayments);
    }

    public function renderPendingPage(Order $ppOrder): void
    {
        $this->frontend->renderPayPalJsSDK([Settings::COMPONENT_BUTTONS], [], $this->getPaymentMethod()->getBNCode());
    }
}
