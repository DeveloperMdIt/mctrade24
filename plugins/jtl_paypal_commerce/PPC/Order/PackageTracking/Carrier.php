<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking;

/**
 * Class Carrier
 * @package Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking
 */
class Carrier
{
    public const CARRIER_DE_POST        = 'DEUTSCHE_DE'; // Deutsche Post
    public const CARRIER_DE_DHL_EXPRESS = 'DHL'; // DHL Express
    public const CARRIER_DE_DHL_PACKET  = 'DHL'; // DHL Packet
    public const CARRIER_DE_DPD         = 'DE_DPD_DELISTRACK'; // DPD Germany
    public const CARRIER_DE_GLS         = 'GLS'; // General Logistics Systems (GLS)
    public const CARRIER_DE_HERMES      = 'HERMES_DE'; // Hermes Germany
    public const CARRIER_DE_TNT         = 'TNT'; // TNT Germany
    public const CARRIER_DE_UPS         = 'UPS'; // United Parcel Service
    public const CARRIER_DPE_EXPRESS    = 'DPE_EXPRESS'; // DPE Express
    public const CARRIER_FEDEX          = 'FEDEX'; // Federal Express.
    public const CARRIER_OTHER          = 'OTHER'; // Other.

    public const CARRIERS = [
        self::CARRIER_DE_POST,
        self::CARRIER_DE_DHL_EXPRESS,
        self::CARRIER_DE_DHL_PACKET,
        self::CARRIER_DE_DPD,
        self::CARRIER_DE_GLS,
        self::CARRIER_DE_HERMES,
        self::CARRIER_DE_TNT,
        self::CARRIER_DE_UPS,
        self::CARRIER_DPE_EXPRESS,
        self::CARRIER_FEDEX,
        self::CARRIER_OTHER,
    ];
}
