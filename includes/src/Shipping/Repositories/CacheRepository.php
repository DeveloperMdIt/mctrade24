<?php

declare(strict_types=1);

namespace JTL\Shipping\Repositories;

use JTL\Cache\JTLCacheInterface;
use JTL\Country\Country;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Shipping\DomainObjects\PaymentDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shipping\DomainObjects\ShippingSurchargeDTO;
use JTL\Shop;

/**
 * Class CacheRepository
 *
 * @package JTL\Shipping\Repositories
 * @since 5.5.0
 * @description This is a layer between the Shipping Service and the cache.
 */
class CacheRepository
{
    private const FREE_SHIPPING_COUNTRIES_CACHE_ID = 'bvkfls_';
    private const FREE_SHIPPING_CACHE_ID           = 'vkfrei_';
    private const SHIPPING_METHODS_CACHE_ID        = 'shipmet_';
    private const PAYMENT_METHODS_CACHE_ID         = 'paymet_';
    private const SHIPPING_SURCHARGES_CACHE_ID     = 'shisur_';
    private JTLCacheInterface $cache;
    private CountryServiceInterface $countryService;

    public function __construct(?JTLCacheInterface $cache = null, ?CountryServiceInterface $countryService = null)
    {
        $this->countryService = $countryService ?? Shop::Container()->getCountryService();
        $this->cache          = $cache ?? Shop::Container()->getCache();
    }

    public function get(string $cacheID, ?callable $callback = null, mixed $customData = null): mixed
    {
        return $this->cache->isActive()
            ? $this->cache->get($cacheID, $callback, $customData)
            : null;
    }

    /**
     * @param string[] $tags
     */
    public function set(string $cacheID, mixed $content, array|string|null $tags = null, ?int $expiration = null): bool
    {
        return $this->cache->isActive() && $this->cache->set($cacheID, $content, $tags, $expiration);
    }

    /**
     * @return string[]
     */
    public function getFreeShippingCountries(ShippingDTO $shippingMethod, int $langID): array
    {
        $offset = \hash(
            'md5',
            self::FREE_SHIPPING_COUNTRIES_CACHE_ID
            . $shippingMethod->freeShippingMinAmount
            . \count($shippingMethod->countries)
            . $langID
        );

        $result = $this->get($offset);
        if (\is_string($result) === false) {
            return [];
        }

        return \array_filter(
            \explode(',', $result)
        );
    }

    /**
     * @return string[]
     */
    public function setFreeShippingCountries(ShippingDTO $shippingMethod, int $langID): array
    {
        $shippingFreeCountries = $this->countryService
            ->getFilteredCountryList($shippingMethod->countries)
            ->map(
                static function ($country) use ($langID): string {
                    return $country instanceof Country
                        ? $country->getName($langID)
                        : '';
                }
            )->all();

        $offset = \hash(
            'md5',
            self::FREE_SHIPPING_COUNTRIES_CACHE_ID
            . $shippingMethod->freeShippingMinAmount
            . \count($shippingMethod->countries)
            . $langID
        );
        $this->set(
            $offset,
            \implode(',', $shippingFreeCountries),
            [\CACHING_GROUP_SHIPPING]
        );

        return $shippingFreeCountries;
    }

    /**
     * @return PaymentDTO[]
     */
    public function getPaymentMethods(int $shippingMethodID, int $customerGroupID): array
    {
        $offset = \hash(
            'md5',
            self::PAYMENT_METHODS_CACHE_ID
            . $customerGroupID
            . $shippingMethodID
        );

        $paymentMethods = $this->get($offset);
        if (\is_array($paymentMethods) === false) {
            return [];
        }

        return $paymentMethods;
    }

    /**
     * @param PaymentDTO[] $paymentMethods
     * @return PaymentDTO[]
     */
    public function setPaymentMethods(int $shippingMethodID, int $customerGroupID, array $paymentMethods): array
    {
        $paymentMethods = \array_filter(
            $paymentMethods,
            static function ($paymentMethod) {
                return $paymentMethod instanceof PaymentDTO;
            }
        );

        $offset = \hash(
            'md5',
            self::FREE_SHIPPING_CACHE_ID
            . $customerGroupID
            . $shippingMethodID
        );
        $this->set(
            $offset,
            $paymentMethods,
            [\CACHING_GROUP_SHIPPING],
        );

        return $paymentMethods;
    }

    /**
     * @return ShippingDTO[]
     */
    public function getPossibleMethods(string $deliveryCountryCode, int $customerGroupID): array
    {
        $offset = \hash(
            'md5',
            self::SHIPPING_METHODS_CACHE_ID
            . $deliveryCountryCode
            . $customerGroupID
        );

        $methods = $this->get($offset);
        if (\is_array($methods) === false) {
            return [];
        }

        return $methods;
    }

    /**
     * @param ShippingDTO[] $methods
     * @todo Can lead to massive amount of repeated entries. Should be discussed with the team.
     */
    public function setPossibleMethods(array $methods, string $deliveryCountryCode, int $customerGroupID): bool
    {
        $methods = \array_filter(
            $methods,
            static function ($shippingMethod) {
                return $shippingMethod instanceof ShippingDTO;
            }
        );

        $offset = \hash(
            'md5',
            self::SHIPPING_METHODS_CACHE_ID
            . $deliveryCountryCode
            . $customerGroupID
        );
        $this->set(
            $offset,
            $methods,
            [\CACHING_GROUP_SHIPPING],
        );

        return true;
    }

    public function getShippingSurcharge(
        int $shippingMethodID,
        string $country,
        string $zipCode
    ): ?ShippingSurchargeDTO {
        $offset = \hash(
            'md5',
            self::SHIPPING_SURCHARGES_CACHE_ID
            . $shippingMethodID
            . $country
            . $zipCode
        );

        $surcharge = $this->get($offset);
        if ($surcharge instanceof ShippingSurchargeDTO) {
            return $surcharge;
        }

        return null;
    }

    public function setShippingSurcharge(
        int $shippingMethodID,
        string $country,
        string $zipCode,
        ShippingSurchargeDTO $shippingSurcharge
    ): ShippingSurchargeDTO {
        $offset = \hash(
            'md5',
            self::SHIPPING_SURCHARGES_CACHE_ID
            . $shippingMethodID
            . $country
            . $zipCode
        );
        $this->set(
            $offset,
            $shippingSurcharge,
            [\CACHING_GROUP_SHIPPING],
        );

        return $shippingSurcharge;
    }
}
