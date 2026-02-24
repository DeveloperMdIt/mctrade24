<?php

declare(strict_types=1);

namespace JTL\Shipping\Repositories;

use JTL\Abstracts\AbstractDBRepository;
use JTL\Checkout\DeliveryAddressTemplate;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\ShippingSurchargeArea;
use JTL\Language\LanguageModel;
use JTL\Plugin\State;
use JTL\Shipping\DomainObjects\PaymentDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shipping\Helper\ShippingCalculationMethod;
use stdClass;

/**
 * Class DatabaseRepository
 * @package JTL\Shipping\Repositories
 * @since 5.5.0
 * @description This is a layer between the Shipping Service and the database.
 */
class DatabaseRepository extends AbstractDBRepository
{
    private ?int $cashPaymentID = null;

    /**
     * @var array<int, object{shippingMethodID: int, till: float, price: float}[]>
     */
    private array $bulkPrices = [];

    /**
     * @var array<int, PaymentDTO[]>
     */
    private array $paymentMethods = [];

    public function getTableName(): string
    {
        return 'tversandart';
    }

    public function localizeShippingMethod(ShippingDTO $shippingMethod, LanguageModel $language): ShippingDTO
    {
        $legacyObject = $shippingMethod->toLegacyObject();

        return ShippingDTO::fromLegacyObject(
            $this->initLocalizations([$legacyObject], [$language])[0] ?? $legacyObject
        );
    }

    /**
     * @param stdClass[]      $shippingMethods
     * @param LanguageModel[] $languages
     * @return stdClass[]
     */
    private function initLocalizations(array $shippingMethods, array $languages): array
    {
        $result       = [];
        $translations = [];
        $langCodes    = '';
        foreach ($languages as $language) {
            if ($language->getActive() === 0) {
                continue;
            }
            $langCodes .= '\'' . $language->getCode() . '\',';
        }

        $shippingMethodIDs = \implode(
            ',',
            \array_map(
                static function (stdClass $shippingMethod): int {
                    return (int)$shippingMethod->kVersandart;
                },
                $shippingMethods,
            )
        );

        $whereSQL = $shippingMethodIDs !== ''
            ? 'WHERE tversandartsprache.kVersandart IN (' . $shippingMethodIDs . ')'
            : '';
        if ($langCodes !== '') {
            $whereSQL .= $whereSQL === ''
                ? 'WHERE tversandartsprache.cISOSprache IN (' . \rtrim($langCodes, ',') . ')'
                : ' AND tversandartsprache.cISOSprache IN (' . \rtrim($langCodes, ',') . ')';
        }

        foreach (
            $this->db->getObjects(
                'SELECT tversandartsprache.kVersandart, tversandartsprache.cISOSprache,
                tversandartsprache.cName AS angezeigterName, tversandartsprache.cLieferdauer AS cLieferdauer,
                tversandartsprache.cHinweistext AS angezeigterHinweistextMail,
                tversandartsprache.cHinweistextShop AS angezeigterHinweistextShop
                FROM tversandartsprache ' . $whereSQL,
            ) as $translation
        ) {
            $id  = (int)$translation->kVersandart;
            $iso = $translation->cISOSprache;
            if (!isset($translations[$id])) {
                $translations[$id]                             = new stdClass();
                $translations[$id]->angezeigterName            = [];
                $translations[$id]->angezeigterHinweistextMail = [];
                $translations[$id]->angezeigterHinweistextShop = [];
                $translations[$id]->cLieferdauer               = [];
            }
            $translations[$id]->angezeigterName[$iso]            = $translation->angezeigterName;
            $translations[$id]->angezeigterHinweistextMail[$iso] = $translation->angezeigterHinweistextMail;
            $translations[$id]->angezeigterHinweistextShop[$iso] = $translation->angezeigterHinweistextShop;
            $translations[$id]->cLieferdauer[$iso]               = $translation->cLieferdauer;
        }

        foreach ($shippingMethods as $shippingMethod) {
            $id = (int)$shippingMethod->kVersandart;

            $result[$id] = (object)\array_merge(
                (array)$shippingMethod,
                (array)($translations[$id] ?? []),
            );
        }

        return \array_values($result);
    }

    private function getCashPaymentID(): int
    {
        if ($this->cashPaymentID === null) {
            $this->cashPaymentID = $this->db->getSingleInt(
                'SELECT kZahlungsart
                FROM tzahlungsart
                WHERE cModulId = :cModulId',
                'kZahlungsart',
                ['cModulId' => 'za_barzahlung_jtl']
            );
        }

        return $this->cashPaymentID;
    }

    /**
     * @param LanguageModel[] $languages
     * @return ShippingDTO[]
     */
    public function getShippingMethods(
        int $customerGroupID,
        array $languages,
        string $countryISOCode,
        bool $getBulkPrices = true,
        bool $getLocalized = true
    ): array {
        $shippingMethods = $this->db->getObjects(
            'SELECT tversandart.*
                FROM tversandart
                WHERE tversandart.cLaender LIKE :iso
                    AND (
                        tversandart.cKundengruppen = \'-1\'
                        OR FIND_IN_SET(:cGroupID, REPLACE(tversandart.cKundengruppen, \';\', \',\')) > 0
                    ) ORDER BY tversandart.nSort',
            [
                'iso'      => '%' . $countryISOCode . '%',
                'cGroupID' => $customerGroupID,
            ]
        );
        if ($getLocalized) {
            $shippingMethods = $this->initLocalizations(
                $shippingMethods,
                $languages
            );
        }

        $result = [];
        foreach ($shippingMethods as $shippingMethod) {
            if (
                $getBulkPrices
                && $shippingMethod->kVersandberechnung
                !== ShippingCalculationMethod::VM_VERSANDKOSTEN_PAUSCHALE_JTL->value
            ) {
                $shippingMethod->bulkPrices = $this->bulkPrices[(int)$shippingMethod->kVersandart]
                    ?? $this->getBulkPrices((int)$shippingMethod->kVersandart);

                $this->bulkPrices[(int)$shippingMethod->kVersandart] = $shippingMethod->bulkPrices;
            }
            $shippingMethod->paymentMethods = $this->paymentMethods[(int)$shippingMethod->kVersandart]
                ?? $this->getPaymentMethods(
                    (int)$shippingMethod->kVersandart,
                    $customerGroupID
                );

            $this->paymentMethods[(int)$shippingMethod->kVersandart] = $shippingMethod->paymentMethods;

            $shippingMethod->CashPayment = \in_array(
                'za_barzahlung_jtl',
                \array_column(
                    $shippingMethod->paymentMethods,
                    'modulID'
                ),
                true
            );

            $result[] = ShippingDTO::fromLegacyObject($shippingMethod);
        }

        return $result;
    }

    /**
     * @param LanguageModel[] $languages
     * @return ShippingDTO[]
     */
    public function getAllShippingMethods(array $languages): array
    {
        $shippingMethods = $this->initLocalizations(
            $this->db->getObjects('SELECT * FROM tversandart'),
            $languages
        );

        return \array_map(
            static function (stdClass $data): ShippingDTO {
                return ShippingDTO::fromLegacyObject($data);
            },
            $shippingMethods,
        );
    }

    /**
     * @return ShippingDTO[]
     */
    public function getFreeShippingMethods(int $customerGroupID, int $shippingClassID): array
    {
        $shippingMethods = $this->db->getObjects(
            "SELECT *
                FROM tversandart
                WHERE fVersandkostenfreiAbX > 0
                    AND (cVersandklassen = '-1'
                    OR cVersandklassen RLIKE :sClasses)
                    AND (cKundengruppen = '-1' OR FIND_IN_SET(:cGroupID, REPLACE(cKundengruppen, ';', ',')) > 0)",
            [
                'sClasses' => '^([0-9 -]* )?' . $shippingClassID . ' ',
                'cGroupID' => $customerGroupID,
            ]
        );

        return \array_map(
            static function (stdClass $data): ShippingDTO {
                return ShippingDTO::fromLegacyObject($data);
            },
            $shippingMethods,
        );
    }

    /**
     * @return PaymentDTO[]
     */
    public function getPaymentMethods(int $shippingMethodID, int $customerGroupID): array
    {
        $paymentMethods = $this->db->getObjects(
            'SELECT tversandartzahlungsart.*, tzahlungsart.*
                 FROM tversandartzahlungsart, tzahlungsart
                 LEFT JOIN tpluginzahlungsartklasse
                     ON tpluginzahlungsartklasse.cModulId = tzahlungsart.cModulId
                 LEFT JOIN tplugin
                     ON tplugin.kPlugin = tpluginzahlungsartklasse.kPlugin
                 WHERE tversandartzahlungsart.kVersandart = :methodID
                   AND tversandartzahlungsart.kZahlungsart = tzahlungsart.kZahlungsart
                   AND COALESCE(tplugin.nStatus, :PluginStateActivated) = :PluginStateActivated
                   AND (
                       tzahlungsart.cKundengruppen IS NULL
                       OR tzahlungsart.cKundengruppen = \'\'
                       OR FIND_IN_SET(:cGroupID, REPLACE(tzahlungsart.cKundengruppen, \';\', \',\')) > 0
                   )
                   AND tzahlungsart.nActive = 1
                   AND tzahlungsart.nNutzbar = 1
                 ORDER BY tzahlungsart.nSort',
            [
                'methodID'             => $shippingMethodID,
                'cGroupID'             => $customerGroupID,
                'PluginStateActivated' => STATE::ACTIVATED,
            ]
        );

        return \array_map(
            static function (stdClass $data): PaymentDTO {
                return PaymentDTO::fromLegacyObject($data);
            },
            $paymentMethods,
        );
    }

    /**
     * @param string[] $allowedISOCodes
     * @param int[]    $shippingClasses
     * @return array<int, string>
     */
    public function getShippingCountriesByIsoCodes(
        array $allowedISOCodes,
        int $customerGroupID,
        array $shippingClasses,
        bool $onlyDependentMethods,
    ): array {
        $result = [];
        foreach (
            $this->db->getObjects(
                'SELECT cLaender, (tversandartzahlungsart.kZahlungsart IS NOT NULL) as CashPayment
                FROM tversandart
                LEFT JOIN tversandartzahlungsart
                    ON tversandartzahlungsart.kVersandart = tversandart.kVersandart
                       AND tversandartzahlungsart.kZahlungsart = :cashPaymentID
                WHERE tversandart.cNurAbhaengigeVersandart = :depOnly
                    AND (
                        tversandart.cKundengruppen = \'-1\'
                        OR FIND_IN_SET(:cGroupID, REPLACE(tversandart.cKundengruppen, \';\', \',\')) > 0
                    )
                    AND (
                        tversandart.cVersandklassen = \'-1\'
                        OR tversandart.cVersandklassen RLIKE :sClasses
                    )',
                [
                    'cGroupID'      => $customerGroupID,
                    'sClasses'      => '^([0-9 -]* )?' . \implode('-', $shippingClasses) . ' ',
                    'depOnly'       => $onlyDependentMethods ? 'Y' : 'N',
                    'cashPaymentID' => $this->getCashPaymentID(),
                ]
            ) as $shippingMethod
        ) {
            foreach (
                \explode(
                    ' ',
                    $shippingMethod->cLaender
                ) as $methodsISOCode
            ) {
                if (
                    $methodsISOCode === ''
                    || \in_array($methodsISOCode, $result, true)
                    || (
                        $allowedISOCodes !== []
                        && !\in_array($methodsISOCode, $allowedISOCodes, true)
                    )
                ) {
                    continue;
                }
                $result[] = $methodsISOCode;
            }
        }

        \sort($result);

        return $result;
    }

    /**
     * @return array<int, object{kVerpackung: int, kSteuerklasse: int, cName: string, cKundengruppe: string,
     *     fBrutto: float, fMindestbestellwert: float, fKostenfrei: float, nAktiv: int, kVerpackungSprache: int,
     *     cISOSprache: string, cBeschreibung: string}>
     */
    public function getPackagings(string $langISOCode, int $customerGroupID, float $cartSumGross): array
    {
        return \array_map(
            static function (stdClass $row): object {
                return (object)[
                    'kVerpackung'         => (int)$row->kVerpackung,
                    'kSteuerklasse'       => (int)$row->kSteuerklasse,
                    'cName'               => (string)$row->cName,
                    'cKundengruppe'       => (string)$row->cKundengruppe,
                    'fBrutto'             => (float)$row->fBrutto,
                    'fMindestbestellwert' => (float)$row->fMindestbestellwert,
                    'fKostenfrei'         => (float)$row->fKostenfrei,
                    'nAktiv'              => (int)$row->nAktiv,
                    'kVerpackungSprache'  => (int)$row->kVerpackungSprache,
                    'cISOSprache'         => (string)$row->cISOSprache,
                    'cBeschreibung'       => (string)$row->cBeschreibung,
                ];
            },
            $this->db->getObjects(
                "SELECT * FROM tverpackung
                JOIN tverpackungsprache
                    ON tverpackung.kVerpackung = tverpackungsprache.kVerpackung
                WHERE tverpackungsprache.cISOSprache = :lcode
                AND (tverpackung.cKundengruppe = '-1'
                    OR FIND_IN_SET(:cid, REPLACE(tverpackung.cKundengruppe, ';', ',')) > 0)
                AND :csum >= tverpackung.fMindestbestellwert
                AND tverpackung.nAktiv = 1
                ORDER BY tverpackung.kVerpackung",
                [
                    'lcode' => $langISOCode,
                    'cid'   => $customerGroupID,
                    'csum'  => $cartSumGross
                ]
            ),
        );
    }

    /**
     * @return object{till: float, price: float}|null
     */
    public function getBulkPrice(int $id, float $till): ?object
    {
        $queryResult = $this->db->getSingleObject(
            'SELECT *
                FROM tversandartstaffel
                WHERE kVersandart = :sid
                    AND fBis >= :till
                ORDER BY fBis',
            ['sid' => $id, 'till' => $till]
        );

        if ($queryResult === null || (int)$queryResult->kVersandart <= 0) {
            return null;
        }

        return (object)[
            'till'  => (float)$queryResult->fBis,
            'price' => (float)$queryResult->fPreis,
        ];
    }

    /**
     * @return object{shippingMethodID: int, till: float, price: float}[]
     */
    public function getBulkPrices(int $shippingMethodID): array
    {
        $result     = [];
        $bulkPrices = $this->db->getObjects(
            'SELECT tversandartstaffel.fBis, tversandartstaffel.fPreis
                FROM tversandartstaffel
                WHERE kVersandart = :methodID
                ORDER BY fBis',
            ['methodID' => $shippingMethodID]
        );

        if (\count($bulkPrices) === 0) {
            return [];
        }

        foreach ($bulkPrices as $bulkPrice) {
            $result[] = (object)[
                'till'  => (float)$bulkPrice->fBis,
                'price' => (float)$bulkPrice->fPreis,
            ];
        }

        return $result;
    }

    public function getPreferredDeliveryAddress(int $customerID): ?Lieferadresse
    {
        if ($customerID === 0) {
            return null;
        }
        $addressID = $this->db->getSingleInt(
            'SELECT kLieferadresse
                FROM tlieferadressevorlage
                WHERE kKunde = :cid
                    AND nIstStandardLieferadresse = 1',
            'kLieferadresse',
            ['cid' => $customerID]
        );
        if ($addressID > 0) {
            $_SESSION["shippingAddressPresetID"] = $addressID;
            return (new DeliveryAddressTemplate(
                $this->db,
                $addressID
            ))->getDeliveryAddress();
        }

        return null;
    }

    /**
     * @param LanguageModel[] $languages
     * @return stdClass{surchargeID: int, shippingMethodID: int, isoCode: string, name: string, netSurcharge: float,
     *     surchargeLocalized: null, localizedNames: array<string, string>}|null
     */
    public function getSurchargeForShippingMethod(
        int $shippingMethodID,
        string $country,
        string $zipCode,
        array $languages
    ): ?stdClass {
        $result     = null;
        $surcharges = $this->db->getObjects(
            'SELECT tversandzuschlagplz.kVersandzuschlag, cPLZ, cPLZAb, cPLZBis, tversandzuschlag.cISO,
                tversandzuschlag.cName, tversandzuschlag.fZuschlag
                FROM tversandzuschlagplz
                LEFT JOIN tversandzuschlag
                    ON tversandzuschlag.kVersandzuschlag = tversandzuschlagplz.kVersandzuschlag
                        AND tversandzuschlag.kVersandart = :shippingID
                WHERE tversandzuschlag.cISO = :country
                    AND (
                        cPLZ = :zipCode
                        OR cPLZ IS NULL
                        OR cPLZ = \'\'
                    )',
            [
                'shippingID' => $shippingMethodID,
                'zipCode'    => $zipCode,
                'country'    => $country
            ]
        );

        foreach ($surcharges as $surcharge) {
            if ($surcharge->cPLZ === $zipCode) {
                $result = $surcharge;
                break;
            }
            $area = new ShippingSurchargeArea($surcharge->cPLZAb, $surcharge->cPLZBis);
            if ($area->isInArea($zipCode)) {
                $result = $surcharge;
                break;
            }
        }
        if ($result === null) {
            return null;
        }
        $langCodes = \array_map(
            static function (LanguageModel $language): string {
                return $language->getCode();
            },
            $languages
        );

        $surchargeLocalizations = $this->db->getObjects(
            'SELECT cISOSprache, cName
                FROM tversandzuschlagsprache
                WHERE kVersandzuschlag = :surchargeID
                    AND cISOSprache IN (\'' . \implode('\',\'', $langCodes) . '\')',
            ['surchargeID' => (int)$result->kVersandzuschlag]
        );

        $localizedNames = [];
        foreach ($surchargeLocalizations as $surchargeLocalization) {
            $lang = \array_reduce(
                $languages,
                static function (
                    ?LanguageModel $carry,
                    LanguageModel $language
                ) use ($surchargeLocalization): ?LanguageModel {
                    return $language->getCode() === $surchargeLocalization->cISOSprache
                        ? $language
                        : $carry;
                }
            );
            if ($lang === null) {
                return null;
            }

            $localizedNames[$lang->getCode()] = $surchargeLocalization->cName;
        }

        return (object)[
            'surchargeID'        => (int)$result->kVersandzuschlag,
            'shippingMethodID'   => $shippingMethodID,
            'isoCode'            => $result->cISO,
            'name'               => $result->cName,
            'netSurcharge'       => (float)$result->fZuschlag,
            'surchargeLocalized' => null,
            'localizedNames'     => $localizedNames
        ];
    }
}
