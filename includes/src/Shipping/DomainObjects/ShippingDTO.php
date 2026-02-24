<?php

declare(strict_types=1);

namespace JTL\Shipping\DomainObjects;

use Illuminate\Support\Collection;
use JTL\Abstracts\AbstractDataTransferObject;
use JTL\Checkout\ShippingSurcharge;
use JTL\Checkout\Versandart;
use JTL\Country\Country;
use JTL\Shipping\Helper\ShippingCalculationMethod;
use JTL\Shop;
use RuntimeException;
use stdClass;

/**
 * Class ShippingDTO
 * @package JTL\Shipping
 * @description Data container for Shipping Method Objects used in the JTL Shop Core
 */
class ShippingDTO extends AbstractDataTransferObject
{
    protected const CACHE_ID            = 'smeth_';
    protected const ALLOW_DYNAMIC_PROPS = true;

    protected const MAPPING = [
        'id'                     => 'kVersandart',
        'calculationType'        => 'kVersandberechnung',
        'allowedShippingClasses' => 'cVersandklassen',
        'name'                   => 'cName',
        'localizedNames'         => 'angezeigterName',
        'localizedHintsShop'     => 'angezeigterHinweistextShop',
        'localizedHintsMail'     => 'angezeigterHinweistextMail',
        'localizedDelivery'      => 'cLieferdauer',
        'countries'              => 'cLaender',
        'showAlways'             => 'cAnzeigen',
        'customerGroups'         => 'cKundengruppen',
        'image'                  => 'cBild',
        'isDependent'            => 'cNurAbhaengigeVersandart',
        'sort'                   => 'nSort',
        'price'                  => 'fPreis',
        'bulkPrices'             => 'bulkPrices',
        'freeShippingMinAmount'  => 'fVersandkostenfreiAbX',
        'maxPrice'               => 'fDeckelung',
        'sendConfirmationMail'   => 'cSendConfirmationMail',
        'exclFromCheapestCalc'   => 'cIgnoreShippingProposal',
        'minDeliveryDays'        => 'nMinLiefertage',
        'maxDeliveryDays'        => 'nMaxLiefertage',
        'includeTaxes'           => 'eSteuer',
        'localizedPrice'         => 'cPreisLocalized',
        'shippingSurcharge'      => 'Zuschlag',
        'finalNetCost'           => 'fEndpreis',
        'finalGrossCost'         => 'finalGrossCost',
        'customShippingCosts'    => 'specificShippingcosts_arr',
        'paymentMethods'         => 'paymentMethods',
        'hasValidPaymentMethod'  => 'valid',
        'acceptsCashPayment'     => 'CashPayment',
    ];

    /**
     * @param string[]                           $allowedShippingClasses
     * @param string[]                           $localizedNames
     * @param string[]                           $localizedHintsMail
     * @param string[]                           $localizedHintsShop
     * @param string[]                           $localizedDelivery
     * @param string[]                           $countries
     * @param string[]                           $customerGroups
     * @param object{till: float,price: float}[] $bulkPrices
     * @param ShippingCartPositionDTO[]          $customShippingCosts
     * @param PaymentDTO[]                       $paymentMethods
     */
    public function __construct(
        public readonly int $id,
        public readonly ShippingCalculationMethod $calculationType,
        public readonly array $allowedShippingClasses,
        public readonly string $name,
        public readonly array $localizedNames,
        public readonly array $localizedHintsMail,
        public readonly array $localizedHintsShop,
        public readonly array $localizedDelivery,
        public readonly array $countries,
        public readonly bool $showAlways,
        public readonly array $customerGroups,
        public readonly string $image,
        public readonly bool $isDependent,
        public readonly int $sort,
        public readonly float $price,
        public readonly array $bulkPrices,
        public readonly float $freeShippingMinAmount,
        public readonly float $maxPrice,
        public readonly bool $sendConfirmationMail,
        public readonly bool $exclFromCheapestCalc,
        public readonly int $minDeliveryDays,
        public readonly int $maxDeliveryDays,
        public readonly bool $includeTaxes,
        public readonly array $paymentMethods,
        public readonly bool $hasValidPaymentMethod,
        public readonly bool $acceptsCashPayment,
        public readonly string $localizedPrice = '',
        public readonly float $finalNetCost = -1.00,
        public readonly float $finalGrossCost = -1.00,
        public readonly array $customShippingCosts = [],
        public readonly ?ShippingSurchargeDTO $shippingSurcharge = null,
        ?stdClass $legacyData = null,
    ) {
        $legacyObject = new stdClass();
        if ($legacyData !== null) {
            foreach (self::MAPPING as $legacyOffset) {
                $legacyObject->$legacyOffset = $legacyData->$legacyOffset ?? null;
            }
            $legacyObject->cPriceLocalized        = $legacyObject->cPreisLocalized ?? null;
            $legacyObject->angezeigterHinweistext = $legacyObject->angezeigterHinweistextShop ?? [];
        }

        parent::__construct($legacyObject);
    }

    /**
     * @inheritdoc
     */
    public static function fromLegacyObject(stdClass $data): self
    {
        $shippingSurcharge = self::castArray($data, self::MAPPING['shippingSurcharge']);
        $paymentMethods    = self::castArray($data, self::MAPPING['paymentMethods']);
        $data              = self::typecastLegacyData(
            $data,
            [
                'kVersandart'           => 'int',
                'kVersandberechnung'    => 'int',
                'nSort'                 => 'int',
                'nMinLiefertage'        => 'int',
                'nMaxLiefertage'        => 'int',
                'fDeckelung'            => 'float',
                'fVersandkostenfreiAbX' => 'float',
                'fPreis'                => 'float',
            ]
        );

        return self::fromArray([
            'id'                    => self::castInt($data, self::MAPPING['id']),
            'calculationType'       => ShippingCalculationMethod::from(
                (int)($data->kVersandberechnung ?? 1)
            ),
            'allowedShippingClasses' => \array_filter(
                \explode(
                    ' ',
                    self::castString($data, self::MAPPING['allowedShippingClasses'])
                )
            ),
            'name'                  => self::castString($data, self::MAPPING['name']),
            'localizedNames'        => self::castArray($data, self::MAPPING['localizedNames']),
            'localizedHintsMail'    => self::castArray($data, self::MAPPING['localizedHintsMail']),
            'localizedHintsShop'    => self::castArray($data, self::MAPPING['localizedHintsShop']),
            'localizedDelivery'     => self::castArray($data, self::MAPPING['localizedDelivery']),
            'countries'             => \array_filter(
                \explode(
                    ' ',
                    self::castString($data, self::MAPPING['countries'])
                )
            ),
            'showAlways'            => self::castBool($data, self::MAPPING['showAlways'], ['immer']),
            'customerGroups'        => \array_filter(
                \explode(';', self::castString($data, self::MAPPING['customerGroups']))
            ),
            'image'                 => self::castString($data, self::MAPPING['image']),
            'isDependent'           => self::castBool($data, self::MAPPING['isDependent']),
            'sort'                  => self::castInt($data, self::MAPPING['sort']),
            'price'                 => self::castFloat($data, self::MAPPING['price']),
            'bulkPrices'            => self::castArray($data, 'bulkPrices'),
            'freeShippingMinAmount' => self::castFloat($data, self::MAPPING['freeShippingMinAmount']),
            'maxPrice'              => self::castFloat($data, self::MAPPING['maxPrice']),
            'sendConfirmationMail'  => self::castBool($data, self::MAPPING['sendConfirmationMail']),
            'exclFromCheapestCalc'  => self::castBool($data, self::MAPPING['exclFromCheapestCalc']),
            'minDeliveryDays'       => self::castInt($data, self::MAPPING['minDeliveryDays']),
            'maxDeliveryDays'       => self::castInt($data, self::MAPPING['maxDeliveryDays']),
            'includeTaxes'          => self::castBool($data, self::MAPPING['includeTaxes'], ['brutto']),
            'paymentMethods'        => $paymentMethods,
            'hasValidPaymentMethod' => \count($paymentMethods) > 0,
            'acceptsCashPayment'    => self::castBool($data, self::MAPPING['acceptsCashPayment']),
            'shippingSurcharge'     => $shippingSurcharge !== []
                ? ShippingSurchargeDTO::fromLegacyObject((object)$shippingSurcharge)
                : null,
            'legacyData'            => $data,
        ]);
    }

    /**
     * @param object{finalNetCost: float, finalGrossCost: float, localizedPrice: string} $prices
     */
    public function setPrices(object $prices): self
    {
        if (
            ($this->finalNetCost !== -1.00 && $prices->finalNetCost > 0)
            || ($this->finalGrossCost !== -1.00 && $prices->finalGrossCost > 0)
        ) {
            throw new RuntimeException(
                'The properties -finalNetCost and finalGrossCost- can only be set once!'
            );
        }

        $result = self::fromArray(
            \array_merge(
                $this->toArray(),
                [
                    'finalNetCost'   => $prices->finalNetCost,
                    'finalGrossCost' => $prices->finalGrossCost,
                    'localizedPrice' => $prices->localizedPrice,
                ],
            )
        );

        if ($result->legacyObject === null) {
            $result->legacyObject = new stdClass();
        }
        $result->legacyObject->{self::MAPPING['finalNetCost']}   = (string)$prices->finalNetCost;
        $result->legacyObject->{self::MAPPING['finalGrossCost']} = (string)$prices->finalGrossCost;
        $result->legacyObject->{self::MAPPING['localizedPrice']} = $prices->localizedPrice;
        $result->legacyObject->cPriceLocalized                   = [
            0 => (string)$prices->finalGrossCost,
            1 => (string)$prices->finalNetCost,
        ];

        return $result;
    }

    /**
     * @param ShippingCartPositionDTO[] $shippingCosts
     */
    public function setCustomShippingCosts(array $shippingCosts): self
    {
        if (empty($shippingCosts)) {
            return $this;
        }
        if ($this->customShippingCosts !== []) {
            throw new RuntimeException('The property -customShippingCosts- can only be set once!');
        }

        $result = self::fromArray(
            \array_merge(
                $this->toArray(),
                ['customShippingCosts' => $shippingCosts],
            )
        );

        if ($result->legacyObject === null) {
            $result->legacyObject = new stdClass();
        }
        $result->legacyObject->{self::MAPPING['customShippingCosts']} = \array_map(
            static function (ShippingCartPositionDTO $position): stdClass {
                return $position->toLegacyObject();
            },
            $shippingCosts
        );

        return $result;
    }

    public function setShippingSurcharge(ShippingSurchargeDTO $shippingSurcharge): self
    {
        if ($this->shippingSurcharge !== null) {
            throw new RuntimeException('The property -shippingSurcharge- can only be set once!');
        }
        $result = self::fromArray(
            \array_merge(
                $this->toArray(),
                ['shippingSurcharge' => $shippingSurcharge],
            )
        );

        if ($result->legacyObject === null) {
            $result->legacyObject = new stdClass();
        }
        $result->legacyObject->{self::MAPPING['shippingSurcharge']} = $shippingSurcharge->toLegacyObject();

        return $result;
    }

    public function toVersandart(string $countryCode): Versandart
    {
        $result                           = new Versandart();
        $result->kVersandart              = $this->id;
        $result->kVersandberechnung       = $this->calculationType->value;
        $result->cName                    = $this->name;
        $result->cLaender                 = \implode(' ', $this->countries);
        $result->cVersandklassen          = \implode(' ', $this->allowedShippingClasses);
        $result->cAnzeigen                = $this->showAlways ? 'immer' : 'guenstigste';
        $result->cKundengruppen           = \implode(';', $this->customerGroups);
        $result->cBild                    = $this->image;
        $result->cNurAbhaengigeVersandart = $this->isDependent ? 'Y' : 'N';
        $result->nSort                    = $this->sort;
        $result->fVersandkostenfreiAbX    = (string)$this->freeShippingMinAmount;
        $result->fDeckelung               = (string)$this->maxPrice;
        $result->cSendConfirmationMail    = $this->sendConfirmationMail ? 'Y' : 'N';
        $result->cIgnoreShippingProposal  = $this->exclFromCheapestCalc ? 'Y' : 'N';
        $result->nMinLiefertage           = $this->minDeliveryDays;
        $result->nMaxLiefertage           = $this->maxDeliveryDays;
        $result->eSteuer                  = $this->includeTaxes ? 'brutto' : 'netto';
        $result->fPreis                   = (string)$this->price;
        $result->country                  = new Country($countryCode);
        $result->shippingSurcharges       = new Collection(
            $this->shippingSurcharge === null
                ? []
                : [new ShippingSurcharge($this->shippingSurcharge->surchargeID)]
        );
        $result->cPriceLocalized          = [
            0 => (string)$this->finalGrossCost,
            1 => (string)$this->finalNetCost,
        ];

        return $result;
    }

    /**
     * @comment Needed for interacting with legacy code
     */
    public function __get(string $offset): mixed
    {
        if ($offset === 'country') {
            return new Country(
                empty($_SESSION['cLieferlandISO'])
                    ? (Shop::Container()->getShippingService()->getDeliveryAddress()->cLand ?? '')
                    : (string)$_SESSION['cLieferlandISO']
            );
        }

        return parent::__get($offset);
    }
}
