<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Plugin\Payment\MethodInterface;
use JTL\Plugin\PluginInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Patch;
use Plugin\jtl_paypal_commerce\PPC\Order\ShippingChangeResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class PayPalPaymentInterface
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
interface PayPalPaymentInterface extends MethodInterface
{
    /**
     * @return bool
     */
    public function paymentDuringOrderSupported(): bool;

    /**
     * @return bool
     */
    public function paymentAfterOrderSupported(): bool;

    /**
     * @param string|null $isoCode
     * @return string
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string;

    /**
     * @param string|null $isoCode
     * @return string
     */
    public function getLocalizedPaymentName(?string $isoCode = null): string;

    /**
     * @return string
     */
    public function getSettingPanel(): string;

    /**
     * @param PaymentMethod $method
     * @param array|null    $settings
     * @return bool
     */
    public function validatePaymentConfiguration(PaymentMethod $method, ?array &$settings = null): bool;

    /**
     * @param Customer           $customer
     * @param Lieferadresse|null $shippingAdr
     * @param Cart|null          $cart
     * @throws InvalidPayerDataException
     */
    public function validatePayerData(Customer $customer, ?Lieferadresse $shippingAdr = null, ?Cart $cart = null): void;

    /**
     * @param bool $onlyCheck
     * @return bool
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool;

    /**
     * @param PluginInterface $plugin
     * @param bool            $force
     * @return NotificationEntry|null
     */
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry;

    /**
     * @param JTLSmarty       $smarty
     * @param PluginInterface $plugin
     * @return void
     */
    public function renderBackendInformation(JTLSmarty $smarty, PluginInterface $plugin): void;

    /**
     * @param string $shippingClasses
     * @param int    $customerGroupID
     * @param int    $shippingMethodID
     * @return bool
     */
    public function isAssigned(string $shippingClasses = '', int $customerGroupID = 0, int $shippingMethodID = 0): bool;

    /**
     * @return Logger
     */
    public function getLogger(): Logger;

    /**
     * @return PaymentMethod
     */
    public function getMethod(): PaymentMethod;

    /**
     * @param Configuration $config
     * @param JTLSmarty     $smarty
     * @return PaymentFrontendInterface
     */
    public function getFrontendInterface(Configuration $config, JTLSmarty $smarty): PaymentFrontendInterface;

    /**
     * @param Order|null $order
     */
    public function storePPOrder(?Order $order): void;

    /**
     * @param string|null $orderId
     * @return Order|null
     */
    public function getPPOrder(?string $orderId = null): ?Order;

    /**
     * @param Customer $customer
     * @param Cart     $cart
     * @param string   $fundingSource
     * @param string   $shippingContext
     * @param string   $payAction
     * @param string   $bnCode
     * @return string|null
     */
    public function createPPOrder(
        Customer $customer,
        Cart $cart,
        string $fundingSource,
        string $shippingContext,
        string $payAction,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): ?string;

    /**
     * @param string $orderId
     * @return Order
     * @throws PPCRequestException | OrderNotFoundException
     */
    public function verifyPPOrder(string $orderId): Order;

    /**
     * @param string|null $orderId
     * @return void
     */
    public function resetPPOrder(?string $orderId = null): void;

    /**
     * @param Order      $ppOrder
     * @param Bestellung $shopOrder
     * @param string     $bnCode
     * @return Order
     */
    public function recreatePPOrder(
        Order $ppOrder,
        Bestellung $shopOrder,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): Order;

    /**
     * @param string $fundingSource
     */
    public function setFundingSource(string $fundingSource): void;

    /**
     * @return string
     */
    public function getFundingSource(): string;

    /**
     * @return string
     */
    public function getDefaultFundingSource(): string;

    /**
     * @param string $bnCode
     * @return void
     */
    public function setBNCode(string $bnCode = MerchantCredentials::BNCODE_CHECKOUT): void;

    /**
     * @param string $default
     * @return string
     */
    public function getBNCode(string $default = MerchantCredentials::BNCODE_CHECKOUT): string;

    /**
     * @param string     $paymentHash
     * @param Bestellung $order
     * @return void
     */
    public function updatePaymentState(string $paymentHash, Bestellung $order): void;

    /**
     * @param Order|null $ppOrder
     * @return string|null
     */
    public function getPaymentStateURL(?Order $ppOrder = null): ?string;

    /**
     * @param Order|null $ppOrder
     * @return string
     */
    public function getPaymentCancelURL(?Order $ppOrder = null): string;

    /**
     * @param Order|null $ppOrder
     * @return string
     */
    public function getPaymentRetryURL(?Order $ppOrder): string;

    /**
     * @param Order $order
     * @return string
     */
    public function getValidOrderState(Order $order): string;

    /**
     * @param string $authAction
     * @return string
     */
    public function get3DSAuthResult(string $authAction): string;

    /**
     * @param Bestellung $shopOrder
     * @return AssignedPayment[]
     */
    public function getAssignedPayments(Bestellung $shopOrder): array;

    /**
     * @param object $customer
     * @param Cart   $cart
     * @return bool
     */
    public function isValidExpressPayment(object $customer, Cart $cart): bool;

    /**
     * @param object       $customer
     * @param Artikel|null $product
     * @return bool
    */
    public function isValidExpressProduct(object $customer, ?Artikel $product): bool;

    /**
     * @param object $customer
     * @param Cart   $cart
     * @return bool
     */
    public function isValidBannerPayment(object $customer, Cart $cart): bool;

    /**
     * @param object       $customer
     * @param Artikel|null $product
     * @return bool
     */
    public function isValidBannerProduct(object $customer, ?Artikel $product): bool;

    /**
     * @param string|null $fundingSource
     * @return bool
     */
    public function isAutoCapture(?string $fundingSource = null): bool;

    /**
     * @param Bestellung $order
     * @return bool
     */
    public function canOrderPayedAgain(Bestellung $order): bool;

    /**
     * @param Bestellung $order
     * @return void
     */
    public function finalizeOrderInDB(Bestellung $order): void;

    /**
     * @return void
     * @throws OrderNotFoundException
     */
    public function onPendingCapture(): void;

    /**
     * @param PaymentStateResult $result
     * @param bool               $timeOut
     * @return void
     */
    public function onPaymentState(PaymentStateResult $result, bool $timeOut = false): void;

    /**
     * @param Order $order
     * @return void
     */
    public function onPaymentComplete(Order $order): void;

    /**
     * @param Order           $order
     * @param Bestellung|null $shopOrder
     * @param bool            $return
     * @return void
     */
    public function handleOrder(Order $order, ?Bestellung $shopOrder = null, bool $return = false): void;

    /**
     * @param string  $eventType
     * @param Capture $capture
     * @param object  $payment
     * @return bool
     */
    public function handleCaptureWebhook(string $eventType, Capture $capture, object $payment): bool;

    /**
     * @param Order                  $order
     * @param ShippingChangeResponse $shippingData
     * @return Patch|null
     */
    public function handleShippingData(Order $order, ShippingChangeResponse $shippingData): ?Patch;

    public function prepareVaultedPayment(string $fundingSource, Customer $customer, string $shippingHash): string;
}
