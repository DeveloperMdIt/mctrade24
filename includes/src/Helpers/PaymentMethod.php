<?php

declare(strict_types=1);

namespace JTL\Helpers;

use JTL\Checkout\Zahlungsart;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class PaymentMethod
 * @package JTL\Helpers
 */
class PaymentMethod
{
    public static function shippingMethodWithValidPaymentMethod(Zahlungsart|stdClass $paymentMethod): bool
    {
        if (!isset($paymentMethod->cModulId)) {
            return false;
        }
        require_once \PFAD_ROOT . \PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $customerID = Frontend::getCustomer()->getID();
        $conf       = Shop::getSettingSection(\CONF_ZAHLUNGSARTEN);

        $paymentMethod->einstellungen = $conf;
        $ok                           = true;
        switch ($paymentMethod->cModulId) {
            case 'za_ueberweisung_jtl':
                $ok = self::checkOrderValues(
                    $conf['zahlungsart_ueberweisung_min_bestellungen'] ?? 0,
                    $conf['zahlungsart_ueberweisung_min'] ?? 0,
                    $conf['zahlungsart_ueberweisung_max'] ?? 0,
                    $customerID
                );
                break;
            case 'za_nachnahme_jtl':
                $ok = self::checkOrderValues(
                    $conf['zahlungsart_nachnahme_min_bestellungen'] ?? 0,
                    $conf['zahlungsart_nachnahme_min'] ?? 0,
                    $conf['zahlungsart_nachnahme_max'] ?? 0,
                    $customerID
                );
                break;
            case 'za_rechnung_jtl':
                $ok = self::checkOrderValues(
                    $conf['zahlungsart_rechnung_min_bestellungen'] ?? 0,
                    $conf['zahlungsart_rechnung_min'] ?? 0,
                    $conf['zahlungsart_rechnung_max'] ?? 0,
                    $customerID
                );
                break;
            case 'za_lastschrift_jtl':
                $ok = self::checkOrderValues(
                    $conf['zahlungsart_lastschrift_min_bestellungen'] ?? 0,
                    $conf['zahlungsart_lastschrift_min'] ?? 0,
                    $conf['zahlungsart_lastschrift_max'] ?? 0,
                    $customerID
                );
                break;
            case 'za_barzahlung_jtl':
                $ok = self::checkOrderValues(
                    $conf['zahlungsart_barzahlung_min_bestellungen'] ?? 0,
                    $conf['zahlungsart_barzahlung_min'] ?? 0,
                    $conf['zahlungsart_barzahlung_max'] ?? 0,
                    $customerID
                );
                break;
            case 'za_null_jtl':
                return true;
            default:
                $payMethod = LegacyMethod::create($paymentMethod->cModulId);
                if ($payMethod !== null) {
                    $ok = $payMethod->isValidIntern([Frontend::getCustomer(), Frontend::getCart()]);
                }
                break;
        }

        return $ok;
    }

    private static function checkOrderValues(
        float|int|string $minOrders,
        float|int|string $minOrderValue,
        float|int|string $maxOrderValue,
        int $customerID
    ): bool {
        return self::checkMinOrders($minOrders, $customerID)
            && self::checkMinOrderValue($minOrderValue)
            && self::checkMaxOrderValue($maxOrderValue);
    }

    public static function checkMinOrders(int $minOrders, int $customerID): bool
    {
        if ($minOrders <= 0) {
            return true;
        }
        if ($customerID <= 0) {
            Shop::Container()->getLogService()->debug('pruefeZahlungsartMinBestellungen erhielt keinen kKunden');

            return false;
        }
        $count = Shop::Container()->getDB()->getSingleObject(
            'SELECT COUNT(*) AS anz
                FROM tbestellung
                WHERE kKunde = :cid
                    AND (cStatus = :s1 OR cStatus = :s2)',
            [
                'cid' => $customerID,
                's1'  => \BESTELLUNG_STATUS_BEZAHLT,
                's2'  => \BESTELLUNG_STATUS_VERSANDT
            ]
        );
        if ($count !== null && $count->anz < $minOrders) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl {cnt} < {min}',
                ['cnt' => (int)$count->anz, 'min' => $minOrders]
            );

            return false;
        }

        return true;
    }

    public static function checkMinOrderValue(float|int|string $minOrderValue): bool
    {
        if (
            $minOrderValue <= 0
            || Frontend::getCart()->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true) >= $minOrderValue
        ) {
            return true;
        }
        Shop::Container()->getLogService()->debug(
            'checkMinOrderValue Bestellwert zu niedrig: Wert {crnt} < {min}',
            ['crnt' => Frontend::getCart()->gibGesamtsummeWaren(true), 'min' => $minOrderValue]
        );

        return false;
    }

    public static function checkMaxOrderValue(float|int|string $maxOrderValue): bool
    {
        if (
            $maxOrderValue > 0
            && Frontend::getCart()->gibGesamtsummeWarenOhne([\C_WARENKORBPOS_TYP_VERSANDPOS], true)
            >= $maxOrderValue
        ) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert {crnt} > {max}',
                ['crnt' => Frontend::getCart()->gibGesamtsummeWaren(true), 'max' => $maxOrderValue]
            );

            return false;
        }

        return true;
    }

    /**
     * @former pruefeZahlungsartNutzbarkeit()
     */
    public static function checkPaymentMethodAvailability(): void
    {
        foreach (
            Shop::Container()->getDB()->selectAll(
                'tzahlungsart',
                'nActive',
                1,
                'kZahlungsart, cModulId, nSOAP, nCURL, nSOCKETS, nNutzbar'
            ) as $paymentMethod
        ) {
            self::activatePaymentMethod($paymentMethod);
        }
    }

    /**
     * Bei SOAP oder CURL => versuche die Zahlungsart auf nNutzbar = 1 zu stellen, falls nicht schon geschehen.
     * Die Fallback-Zahlart 'za_null_jtl' wird immer auf nNutzbar = 0 (zurück-)gesetzt, falls nicht schon geschehen.
     * @former aktiviereZahlungsart()
     */
    public static function activatePaymentMethod(Zahlungsart|stdClass $paymentMethod): bool
    {
        if ((int)$paymentMethod->kZahlungsart === 0) {
            return false;
        }
        if (($paymentMethod->cModulId ?? '') === 'za_null_jtl') {
            $isUsable = 0;
        } elseif (
            (int)$paymentMethod->nSOAP === 0 && (int)$paymentMethod->nCURL === 0 && (int)$paymentMethod->nSOCKETS === 0
        ) {
            $isUsable = 1;
        } elseif ((int)$paymentMethod->nSOAP === 1 && PHPSettings::checkSOAP()) {
            $isUsable = 1;
        } elseif ((int)$paymentMethod->nCURL === 1 && PHPSettings::checkCURL()) {
            $isUsable = 1;
        } elseif ((int)$paymentMethod->nSOCKETS === 1 && PHPSettings::checkSockets()) {
            $isUsable = 1;
        } else {
            $isUsable = 0;
        }
        if (!isset($paymentMethod->nNutzbar) || $paymentMethod->nNutzbar !== $isUsable) {
            Shop::Container()->getDB()->update(
                'tzahlungsart',
                'kZahlungsart',
                (int)$paymentMethod->kZahlungsart,
                (object)['nNutzbar' => $isUsable]
            );
        }

        return $isUsable === 1;
    }
}
