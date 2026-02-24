<?php

declare(strict_types=1);

namespace JTL\Shipping\Helper;

/**
 * @package JTL\Shipping\Helper
 */
enum ProductDependentShippingCosts: int
{
    case NONE = -1;
    case FIX  = 1;
    case BULK = 2;
}
