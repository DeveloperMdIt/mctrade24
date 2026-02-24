<?php

declare(strict_types=1);

namespace JTL\Shipping\DomainObjects;

use JTL\Abstracts\AbstractDataTransferObject;
use JTL\Shipping\Helper\AdditionalFeeType;
use stdClass;

/**
 * Class PaymentDTO
 * @package JTL\Shipping
 * @description Data container for Payment Method Objects used in the JTL Shop Core
 */
class PaymentDTO extends AbstractDataTransferObject
{
    protected const CACHE_ID            = 'pmeth_';
    protected const ALLOW_DYNAMIC_PROPS = true;

    protected const MAPPING = [
        'id'                     => 'kZahlungsart',
        'name'                   => 'cName',
        'modulID'                => 'cModulId',
        'customerGroups'         => 'cKundengruppen',
        'templateAdditionalStep' => 'cZusatzschrittTemplate',
        'templatePlugin'         => 'cPluginTemplate',
        'image'                  => 'cBild',
        'sort'                   => 'nSort',
        'sendMail'               => 'nMailSenden',
        'isActive'               => 'nActive',
        'provider'               => 'cAnbieter',
        'tsCode'                 => 'cTSCode',
        'duringCheckout'         => 'nWaehrendBestellung',
        'curl'                   => 'nCURL',
        'soap'                   => 'nSOAP',
        'sockets'                => 'nSOCKETS',
        'usable'                 => 'nNutzbar',
        'cTemplateFileURL'       => 'templateFileURL',
        'localizedPaymentMethod' => 'oZahlungsmethodeSprache_arr',
        'configPaymentMethod'    => 'oZahlungsmethodeEinstellung_arr',
        'shippingMethodID'       => 'kVersandart',
        'additionalFee'          => 'fAufpreis',
        'additionalFeeType'      => 'cAufpreisTyp',
    ];

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $modulID,
        public readonly array $customerGroups,
        public readonly string $templateAdditionalStep,
        public readonly string $templatePlugin,
        public readonly string $image,
        public readonly int $sort,
        public readonly bool $sendMail,
        public readonly bool $isActive,
        public readonly string $provider,
        public readonly string $tsCode,
        public readonly bool $duringCheckout,
        public readonly bool $curl,
        public readonly bool $soap,
        public readonly bool $sockets,
        public readonly bool $usable,
        public readonly string $cTemplateFileURL,
        public readonly array $localizedPaymentMethod,
        public readonly array $configPaymentMethod,
        public readonly int $shippingMethodID,
        public readonly float $additionalFee,
        public readonly AdditionalFeeType $additionalFeeType,
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
                'kZahlungsart'        => 'int',
                'nSort'               => 'int',
                'nMailSenden'         => 'int',
                'nActive'             => 'int',
                'nWaehrendBestellung' => 'int',
                'nCURL'               => 'int',
                'nSOAP'               => 'int',
                'nSOCKETS'            => 'int',
                'nNutzbar'            => 'int',
                'kVersandart'         => 'int',
                'fAufpreis'           => 'float',
            ]
        );

        return self::fromArray([
            'id'                     => self::castInt($data, 'kZahlungsart'),
            'name'                   => self::castString($data, 'cName'),
            'modulID'                => self::castString($data, 'cModulId'),
            'customerGroups'         => self::castArray($data, 'cKundengruppen'),
            'templateAdditionalStep' => self::castString($data, 'cZusatzschrittTemplate'),
            'templatePlugin'         => self::castString($data, 'cPluginTemplate'),
            'image'                  => self::castString($data, 'cBild'),
            'sort'                   => self::castInt($data, 'nSort'),
            'sendMail'               => self::castBool($data, 'nMailSenden'),
            'isActive'               => self::castBool($data, 'nActive'),
            'provider'               => self::castString($data, 'cAnbieter'),
            'tsCode'                 => self::castString($data, 'cTSCode'),
            'duringCheckout'         => self::castBool($data, 'nWaehrendBestellung'),
            'curl'                   => self::castBool($data, 'nCURL'),
            'soap'                   => self::castBool($data, 'nSOAP'),
            'sockets'                => self::castBool($data, 'nSOCKETS'),
            'usable'                 => self::castBool($data, 'nNutzbar'),
            'cTemplateFileURL'       => self::castString($data, 'templateFileURL'),
            'localizedPaymentMethod' => self::castArray($data, 'oZahlungsmethodeSprache_arr'),
            'configPaymentMethod'    => self::castArray($data, 'oZahlungsmethodeEinstellung_arr'),
            'shippingMethodID'       => self::castInt($data, 'kVersandart'),
            'additionalFee'          => self::castFloat($data, 'fAufpreis'),
            'additionalFeeType'      => AdditionalFeeType::from(self::castString($data, 'cAufpreisTyp')),
            'legacyData'             => $data,
        ]);
    }
}
