<?php

declare(strict_types=1);

namespace JTL\Shipping\Helper;

/**
 * @package JTL\Shipping\Helper
 */
enum AdditionalFeeType: string
{
    case ADDITIONAL_FEE_TYPE_NONE      = '';
    case ADDITIONAL_FEE_TYPE_FIX_PRICE = 'festpreis';
    case ADDITIONAL_FEE_TYPE_PERCENT   = 'prozent';
}
