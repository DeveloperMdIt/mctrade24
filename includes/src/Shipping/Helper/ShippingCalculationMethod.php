<?php

declare(strict_types=1);

namespace JTL\Shipping\Helper;

/**
 * @package JTL\Shipping\Helper
 */
enum ShippingCalculationMethod: int
{
    case VM_VERSANDKOSTEN_PAUSCHALE_JTL         = 1;
    case VM_VERSANDBERECHNUNG_GEWICHT_JTL       = 2;
    case VM_VERSANDBERECHNUNG_WARENWERT_JTL     = 3;
    case VM_VERSANDBERECHNUNG_ARTIKELANZAHL_JTL = 4;
}
