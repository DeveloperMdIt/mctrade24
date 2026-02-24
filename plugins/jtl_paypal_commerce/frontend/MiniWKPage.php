<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Helpers\Request;
use JTL\Session\Frontend;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

/**
 * Class MiniWKPage
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class MiniWKPage extends AbstractPayPalPage
{
    public function hasValidStep(): bool
    {
        return true;
    }

    public function render(JTLSmarty $smarty): void
    {
        if (Request::isAjaxRequest()) {
            return;
        }

        $customer = Frontend::getCustomer();
        $cart     = Frontend::getCart();
        if (empty($cart->PositionenArr)) {
            return;
        }

        $ppcPayments = [];
        foreach ($this->plugin->getPaymentMethods()->getMethods() as $paymentMethod) {
            $ppcPayment = Helper::getInstance($this->plugin)->getPaymentFromID($paymentMethod->getMethodID());
            if ($ppcPayment !== null) {
                $ppcPayments[] = $ppcPayment;
            }
        }

        $vaultingHelper = new VaultingHelper($this->config);
        $ecs            = new ExpressCheckout();
        foreach ($ppcPayments as $ppcPayment) {
            $shippingAddress = $ecs->applyVaultingAddress($vaultingHelper, $customer, $ppcPayment);
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderMiniCartPage($customer, $cart, $shippingAddress ?? Address::createFromOrderAddress(
                           Frontend::getDeliveryAddress()
                       ));
        }
    }
}
