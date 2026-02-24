<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use function Functional\pluck;

/**
 * Class QueryTracking
 * @package Plugin\jtl_search
 */
class QueryTracking
{
    /**
     * @param array $products
     * @return array
     */
    public static function filterProductKeys(array $products): array
    {
        return \array_map('\intval', pluck($products, 'nId'));
    }

    /**
     * @param array $products
     * @param array $productsExist
     * @return int
     */
    public static function addProducts(array $products, array &$productsExist): int
    {
        $i = 0;
        foreach ($products as $product) {
            if (\in_array($product, $productsExist, true)) {
                continue;
            }
            $productsExist[] = $product;
            $i++;
        }

        return $i;
    }

    /**
     * @param array $queryTrackings
     * @return array|null
     */
    public static function orderQueryTrackings(array $queryTrackings): ?array
    {
        if (\count($queryTrackings) === 0) {
            return null;
        }
        $ordered = [];
        foreach ($queryTrackings as $queryTracking) {
            $ordered[$queryTracking->nQueryTracking] = $queryTracking;
        }
        \ksort($ordered);

        return \array_reverse($ordered);
    }
}
