<?php

/** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce;

use JTL\Cart\Cart;
use JTL\Checkout\Adresse;
use JTL\Checkout\OrderHandler;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Session\Frontend;
use JTL\Shipping\DomainObjects\ShippingCartPositionDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shipping\Services\ShippingService;
use JTL\Shop;
use stdClass;

use function Functional\first;
use function Functional\sort;

/**
 * Class LegacyHelper
 * @package Plugin\jtl_paypal_commerce
 */
class LegacyHelper
{
    private static ?ShippingService $shippingService = null;

    private static function getShippingService(): ?ShippingService
    {
        if (self::$shippingService === null && \class_exists(ShippingService::class)) {
            self::$shippingService = Shop::Container()->getShippingService();
        }

        return self::$shippingService;
    }

    /**
     * @return string
     * fixed in 5.4.1
     */
    public static function baueBestellnummer(): string
    {
        $orderHandler = new OrderHandler(
            Shop::Container()->getDB(),
            Frontend::getCustomer(),
            Frontend::getCart()
        );

        return \mb_substr($orderHandler->createOrderNo(), 0, 20);
    }

    private static function getPaymentMethod(int $shippingMethodId, int $paymentMethodId, int $customerGroupId): ?object
    {
        $shippingService = self::getShippingService();
        if ($shippingService === null) {
            return first(ShippingMethod::getPaymentMethods($shippingMethodId, $customerGroupId, $paymentMethodId));
        }

        $paymentMethod = $shippingService->filterPaymentMethodByID(
            $shippingService->getPossiblePaymentMethods($shippingMethodId, $customerGroupId),
            $paymentMethodId
        );

        return $paymentMethod === null ? null : $paymentMethod->toLegacyObject();
    }

    public static function getPaymentSurchargeDiscount(
        int $paymentMethodId,
        int $shippingMethodId,
        int $customerGroupId,
        string $countryCode,
        bool $gross
    ): float {
        $cart      = Frontend::getCart();
        $method    = self::getPaymentMethod($shippingMethodId, $paymentMethodId, $customerGroupId);
        $surcharge = (float)$method->fAufpreis;
        if ($method->cAufpreisTyp === 'prozent') {
            $fGuthaben = $_SESSION['Bestellung']->fGuthabenGenutzt ?? 0;
            $surRec    = $cart->gibGesamtsummeWarenExt([
                \C_WARENKORBPOS_TYP_ARTIKEL,
                \C_WARENKORBPOS_TYP_VERSANDPOS,
                \C_WARENKORBPOS_TYP_KUPON,
                \C_WARENKORBPOS_TYP_GUTSCHEIN,
                \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
                \C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
                \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
                \C_WARENKORBPOS_TYP_VERPACKUNG,
            ], true);
            $surcharge = (($surRec - $fGuthaben) * $method->fAufpreis) / 100.0;
        }
        if (!$gross) {
            $taxRate   = Tax::getSalesTax(
                self::gibVersandkostenSteuerklasse($cart, $countryCode)
            );
            $surcharge = $surcharge / (100 + $taxRate) * 100.0;
        }

        return $surcharge;
    }

    /**
     * @param stdClass[] $shippingMethods
     * @param int        $paymentMethodId
     * @param int        $customerGroupId
     * @return stdClass[]
     */
    private static function getLegacyCheapestShippingMethod(
        array $shippingMethods,
        int $paymentMethodId,
        int $customerGroupId
    ): array {
        /** @var stdClass[] $shippingMethods */
        $shippingMethods = \array_filter(
            $shippingMethods,
            static function (stdClass $method) use ($paymentMethodId, $customerGroupId) {
                $paymentMethods = ShippingMethod::getPaymentMethods(
                    (int)$method->kVersandart,
                    $customerGroupId,
                    $paymentMethodId
                );

                return !empty($paymentMethods);
            }
        );

        return $shippingMethods;
    }

    public static function getCheapestShippingMethod(
        array $shippingMethods,
        int $paymentMethodId,
        int $customerGroupId
    ): ?object {
        if ($paymentMethodId > 0) {
            $shippinService = self::getShippingService();
            if ($shippinService === null) {
                $shippingMethods = self::getLegacyCheapestShippingMethod(
                    $shippingMethods,
                    $paymentMethodId,
                    $customerGroupId
                );
            } else {
                $dtoShippingMethods = \array_map(static function (object $shippingMethod) {
                    if ($shippingMethod instanceof ShippingDTO) {
                        return $shippingMethod;
                    }

                    return ShippingDTO::fromLegacyObject($shippingMethod);
                }, $shippingMethods);

                return $shippinService->getFavourableShippingMethod($dtoShippingMethods);
            }
        }

        return first(sort($shippingMethods, static function (object $a, object $b) {
            if ($a->fEndpreis === $b->fEndpreis) {
                return $a->nSort < $b->nSort ? -1 : 1;
            }

            return $a->fEndpreis < $b->fEndpreis ? -1 : 1;
        }));
    }

    public static function getShippingClasses(Cart $cart): string
    {
        $shippinService = self::getShippingService();
        if ($shippinService === null) {
            return ShippingMethod::getShippingClasses($cart);
        }

        return \implode('-', $shippinService->getShippingClasses($cart->PositionenArr));
    }

    /**
     * @param string $countryCode
     * @param string $zip
     * @param int    $cgroupID
     * @param Cart   $cart
     * @param bool   $legacy
     * @return stdClass[]
     */
    public static function getPossibleShippingMethods(
        string $countryCode,
        string $zip,
        int $cgroupID,
        Cart $cart,
        bool $legacy = true
    ): array {
        $shippinService = self::getShippingService();
        if ($shippinService === null) {
            return ShippingMethod::getPossibleShippingMethods(
                $countryCode,
                $zip,
                self::getShippingClasses($cart),
                $cgroupID
            );
        }


        $possibleMethods = $shippinService->getPossibleShippingMethods(
            Frontend::getCustomer(),
            CustomerGroup::getByID($cgroupID),
            $countryCode,
            Frontend::getCurrency(),
            $zip,
            $cart->PositionenArr
        );

        return $legacy ? \array_map(static function (ShippingDTO $shippingMethod) {
            return $shippingMethod->toLegacyObject();
        }, $possibleMethods) : $possibleMethods;
    }

    /**
     * @param string $country
     * @param array  $items
     * @param bool   $checkDelivery
     * @return stdClass[]
     */
    public static function gibArtikelabhaengigeVersandkostenImWK(
        string $country,
        array $items,
        bool $checkDelivery = true
    ): array {
        $shippinService = self::getShippingService();
        if ($shippinService === null) {
            return ShippingMethod::gibArtikelabhaengigeVersandkostenImWK($country, $items, $checkDelivery);
        }

        $dtoShippingCosts = $shippinService->getCustomShippingCostsByCart(
            $country,
            Frontend::getCustomerGroup(),
            Frontend::getCurrency(),
            $items
        );

        return \array_map(static function (ShippingCartPositionDTO $item) {
            return $item->toLegacyObject();
        }, $dtoShippingCosts);
    }

    public static function gibVersandkostenSteuerklasse(Cart $cart, $countryCode = ''): int
    {
        $shippinService = self::getShippingService();
        if ($shippinService === null) {
            return $cart->gibVersandkostenSteuerklasse($countryCode);
        }

        return $shippinService->getTaxRateIDs('', $cart->PositionenArr, $countryCode)[0]->taxRateID ?? 0;
    }

    public static function isAddressEmpty(Adresse $address): bool
    {
        return empty($address->cPLZ)
            || empty($address->cOrt)
            || empty($address->cLand)
            || empty($address->cStrasse)
            || empty($address->cHausnummer);
    }
}
