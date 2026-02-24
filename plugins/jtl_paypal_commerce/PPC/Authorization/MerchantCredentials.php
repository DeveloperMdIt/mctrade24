<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use Plugin\jtl_paypal_commerce\PPC\ConfigValues;

/**
 * Class MerchantCredentials
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
final class MerchantCredentials
{
    public const BNCODE_CHECKOUT = 'JTL_Cart_Shop5_PPCP_Payments';
    public const BNCODE_EXPRESS  = 'JTL_Cart_Shop5_PPCP_PayShortcut';
    public const BNCODE_ACDC     = self::BNCODE_CHECKOUT;

    /**
     * @param string $workingMode
     * @return string
     */
    public static function partnerID(string $workingMode): string
    {
        static $partnerIDs = [
            ConfigValues::WORKING_MODE_PRODUCTION => 'SzJKWldHUDRKVTNFNg==',
            ConfigValues::WORKING_MODE_SANDBOX    => 'WTlERTgyOUVUM1IzNg==',
        ];

        return $partnerIDs[$workingMode] ?? '';
    }

    /**
     * @param string $workingMode
     * @return string
     */
    public static function partnerClientID(string $workingMode): string
    {
        static $partnerClientIDs = [
            ConfigValues::WORKING_MODE_PRODUCTION => 'QWF1WFFBS1dUOVh4YThKLWN0TkpLclVXbHlmMTBMekYzRzQ4TWMyT3p'
                                                    . 'jNTNTZzNOQXZjOVdDNkZJR1JHX2ZzZm5rYWJMZWtPRnFIT3FDd0c=',
            ConfigValues::WORKING_MODE_SANDBOX    => 'QWZkOHBUWFY0dUFkQnRtR2hNR2hJbmZwODBWaHJfallwLWNOQTlteGd'
                                                    . 'EeHR3T1Axc0dyN2U1SFNuTk9QdmxKcWRDX1NvYlVNREc3UG9rWDQ=',
        ];

        return $partnerClientIDs[$workingMode] ?? '';
    }
}
