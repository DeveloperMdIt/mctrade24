<?php

declare(strict_types=1);

namespace JTL\Shipping\Repositories;

use JTL\Abstracts\AbstractSessionRepository;
use JTL\Cart\Cart;
use JTL\Checkout\Kupon;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Shipping\DomainObjects\ShippingDTO;

/**
 * Class SessionRepository
 *
 * @package JTL\Shipping\Repositories
 * @since 5.5.0
 * @description This is a layer between the Shipping Service and the PHP session.
 */
class SessionRepository extends AbstractSessionRepository
{
    protected const ALLOWED_SESSION_VARS = [
        'shipping_count'          => 'int',
        'cLieferlandISO'          => 'string',
        'oVersandfreiKupon'       => Kupon::class,
        'VersandKupon'            => Kupon::class,
        'Steuerland'              => 'string',
        'Warenkorb'               => Cart::class,
        'Lieferadresse'           => Lieferadresse::class,
        'Zahlungsart'             => 'object',
        'possibleShippingMethods' => 'array',
        'Steuersatz'              => 'array',
        'Kundengruppe'            => CustomerGroup::class,
        'orderCount'              => 'int',
    ];

    public function getCart(): ?Cart
    {
        if (!empty($_SESSION['Warenkorb']) && $_SESSION['Warenkorb'] instanceof Cart) {
            return $_SESSION['Warenkorb'];
        }

        if (
            empty($_SESSION['Warenkorb']->kWarenkorb)
            || \is_int($_SESSION['Warenkorb']->kWarenkorb) === false
        ) {
            return null;
        }

        return new Cart($_SESSION['Warenkorb']->kWarenkorb);
    }

    public function getPaymentMethod(): ?object
    {
        $paymentMethod = $_SESSION['Zahlungsart'] ?? null;

        return $paymentMethod !== null
            ? (object)(array)$paymentMethod
            : null;
    }

    public function setDeliveryAddress(Lieferadresse $deliveryAddress): bool
    {
        $_SESSION['Lieferadresse'] = $deliveryAddress;

        return true;
    }

    public function getDeliveryAddress(): ?Lieferadresse
    {
        if (
            !empty($_SESSION['Lieferadresse'])
            && $_SESSION['Lieferadresse'] instanceof Lieferadresse
        ) {
            return $_SESSION['Lieferadresse'];
        }

        if (
            empty($_SESSION['Lieferadresse']->kLieferadresse)
            || \is_int($_SESSION['Lieferadresse']->kLieferadresse) === false
        ) {
            return null;
        }
        return new Lieferadresse($_SESSION['Lieferadresse']->kLieferadresse);
    }

    /**
     * @param ShippingDTO[] $methods
     */
    public function setPossibleMethods(
        array $methods,
        string $deliveryCountryCode,
        string $deliveryZipCode,
        string $cartHash,
    ): bool {
        foreach ($methods as $method) {
            if ($method instanceof ShippingDTO) {
                continue;
            }
            return false;
        }
        // Prevent session bloating.
        unset($_SESSION['possibleShippingMethods']);
        $_SESSION['possibleShippingMethods'][$cartHash . $deliveryCountryCode . $deliveryZipCode] = $methods;

        return true;
    }

    /**
     * @return ShippingDTO[]
     */
    public function getPossibleMethods(
        string $deliveryCountryCode,
        string $deliveryZipCode,
        string $cartHash,
    ): array {
        return \array_filter(
            $_SESSION['possibleShippingMethods'][$cartHash . $deliveryCountryCode . $deliveryZipCode] ?? [],
            static fn($method) => $method instanceof ShippingDTO
        );
    }

    public function getTaxRateByTaxClassID(int $taxClassID): ?float
    {
        return !empty($_SESSION['Steuersatz'][$taxClassID])
        && \is_numeric($_SESSION['Steuersatz'][$taxClassID])
            ? (float)$_SESSION['Steuersatz'][$taxClassID]
            : null;
    }

    /**
     * @return ShippingDTO[]
     */
    public function getPossibleDependentMethods(string $country, string $hash): array
    {
        return \array_filter(
            $_SESSION['dependentShippingMethods'][$country][$hash] ?? [],
            static fn($method) => $method instanceof ShippingDTO
        );
    }

    /**
     * @param ShippingDTO[] $dependentShippingMethods
     */
    public function setPossibleDependentMethods(
        array $dependentShippingMethods,
        string $country,
        string $hash,
    ): bool {
        foreach ($dependentShippingMethods as $method) {
            if ($method instanceof ShippingDTO) {
                continue;
            }
            return false;
        }
        // Prevent session bloating.
        unset($_SESSION['dependentShippingMethods']);
        $_SESSION['dependentShippingMethods'][$hash . $country] = $dependentShippingMethods;

        return true;
    }

    public function getCustomerGroup(): ?CustomerGroup
    {
        $result = $_SESSION['Kundengruppe'] ?? null;
        if ($result instanceof CustomerGroup) {
            return $result;
        }
        return null;
    }

    public function getOrderCount(Customer|null $customer = null): int
    {
        $customer ??= $_SESSION['Kunde'] ?? null;
        if ($customer instanceof Customer === false) {
            return 0;
        }

        return $customer->getOrderCount();
    }
}
