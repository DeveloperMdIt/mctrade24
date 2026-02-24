<?php

declare(strict_types=1);

namespace JTL\Shipping\DomainObjects;

use JTL\Abstracts\AbstractDataTransferObject;
use stdClass;

/**
 * Class ShippingCartPositionDTO
 * @package JTL\Shipping
 * @description Data container for Shipping positions inside the shopping cart
 */
class ShippingCartPositionDTO extends AbstractDataTransferObject
{
    protected const ALLOW_DYNAMIC_PROPS = true;

    protected const MAPPING = [
        'productID'      => 'kArtikel',
        'nameLocalized'  => 'cName',
        'netPrice'       => 'fKosten',
        'priceLocalized' => 'cPreisLocalized',
    ];

    public function __construct(
        public readonly int $productID,
        public readonly array $nameLocalized,
        public readonly int $taxClassID,
        public readonly float $netPrice,
        public readonly string $priceLocalized,
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
                'kArtikel' => 'int',
                'fKosten'  => 'float',
            ]
        );

        return self::fromArray([
            'productID'      => self::castInt($data, self::MAPPING['productID']),
            'nameLocalized'  => self::castArray($data, self::MAPPING['nameLocalized']),
            'taxClassID'     => self::castInt($data, 'taxClassID'),
            'netPrice'       => self::castFloat($data, self::MAPPING['netPrice']),
            'priceLocalized' => self::castString($data, self::MAPPING['priceLocalized']),
            'legacyData'     => $data,
        ]);
    }
}
