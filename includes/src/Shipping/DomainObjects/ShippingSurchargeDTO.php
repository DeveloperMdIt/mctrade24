<?php

declare(strict_types=1);

namespace JTL\Shipping\DomainObjects;

use JTL\Abstracts\AbstractDataTransferObject;
use stdClass;

/**
 * Class ShippingSurchargeDTO
 * @package JTL\Shipping
 * @description Data container for Shipping Surcharge Objects used in the JTL Shop Core
 */
class ShippingSurchargeDTO extends AbstractDataTransferObject
{
    protected const ALLOW_DYNAMIC_PROPS = true;

    protected const MAPPING = [
        'surchargeID'        => 'kVersandzuschlag',
        'shippingMethodID'   => 'kVersandart',
        'isoCode'            => 'cIso',
        'name'               => 'cName',
        'netSurcharge'       => 'fZuschlag',
        'surchargeLocalized' => 'cPreisLocalized',
        'localizedNames'     => 'angezeigterName',
    ];

    /**
     * @param string[] $localizedNames
     */
    public function __construct(
        public readonly int $surchargeID,
        public readonly int $shippingMethodID,
        public readonly string $isoCode,
        public readonly string $name,
        public readonly float $netSurcharge,
        public readonly string $surchargeLocalized,
        public readonly array $localizedNames,
        ?stdClass $legacyData = null,
    ) {
        $legacyObject = new stdClass();
        if ($legacyData !== null) {
            foreach (self::MAPPING as $legacyOffset) {
                $legacyObject->$legacyOffset = $legacyData->$legacyOffset ?? null;
            }
        }

        parent::__construct($legacyObject);
    }

    /**
     * @inheritdoc
     */
    public static function fromLegacyObject(stdClass $data): self
    {
        $data = self::typecastLegacyData(
            $data,
            [
                'kVersandzuschlag' => 'int',
                'kVersandart'      => 'int',
                'fZuschlag'        => 'float',
            ]
        );

        return self::fromArray([
            'surchargeID'        => self::castInt($data, self::MAPPING['surchargeID']),
            'shippingMethodID'   => self::castInt($data, self::MAPPING['shippingMethodID']),
            'isoCode'            => self::castString($data, self::MAPPING['isoCode']),
            'name'               => self::castString($data, self::MAPPING['name']),
            'netSurcharge'       => self::castFloat($data, self::MAPPING['netSurcharge']),
            'surchargeLocalized' => self::castString($data, self::MAPPING['surchargeLocalized']),
            'localizedNames'     => self::castArray($data, self::MAPPING['localizedNames']),
            'legacyData'         => $data,
        ]);
    }
}
