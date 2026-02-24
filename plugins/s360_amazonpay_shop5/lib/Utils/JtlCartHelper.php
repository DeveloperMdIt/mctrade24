<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Helpers\ShippingMethod;
use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class JtlCartHelper
 *
 * Helps computing cart things.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class JtlCartHelper {

    protected const FALLBACK_ZIP = '00000';
    protected const FALLBACK_COUNTRY_CODE = 'DE';


    /**
     * @var JtlCartHelper $instance
     */
    private static $instance;

    private function __construct() {
    }

    public static function getInstance(): JtlCartHelper {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the estimated current order amount and the current currency code.
     *
     * The estimated order amount is the current cart amount plus the cost of the cheapest available shipping method for the "current" shipping country.
     *
     * @return null|array - returns
     */
    public function getEstimatedOrderAmount(): ?array {
        $currency = Frontend::getCurrency();
        if(!\in_array($currency->getCode(), Currency::AMAZON_SUPPORTED_CURRENCIES, true)) {
            return null;
        }
        $amount = Frontend::getCart()->gibGesamtsummeWaren(true, false) * $currency->getConversionFactor();
        if(!$this->cartHasShippingMethod()) {
            $lowestShippingCost = $this->getMinimumShippingCost($currency);
            $amount += $lowestShippingCost;
        }
        if($amount <= 0.0) {
            return null;
        }
        return [
            'amount' => $amount,
            'currency' => Frontend::getCurrency()->getCode()
        ];
    }

    public function getMinimumShippingCost(\JTL\Catalog\Currency $currency): float {
        $countryCode = Frontend::getCart()->getShippingCountry();
        if(empty($countryCode)) {
            $countryCode = self::FALLBACK_COUNTRY_CODE;
        }
        $zip = Frontend::get('Lieferadresse')->cPLZ ?? Frontend::getCustomer()->cPLZ;
        if(empty($zip)) {
            $zip = self::FALLBACK_ZIP;
        }
        // Note: fEndpreis on these currencies is in the default currency!
        if(Compatibility::isShopAtLeast55()) {
            $shippingMethods = Shop::Container()->getShippingService()->getPossibleShippingMethods(Frontend::getCustomer(), Frontend::getCustomerGroup(), $countryCode, Frontend::getCurrency(), $zip, Frontend::getCart()->PositionenArr);
        } else {
            $shippingMethods = ShippingMethod::getPossibleShippingMethods($countryCode, $zip, ShippingMethod::getShippingClasses(Frontend::getCart()), Frontend::getCustomerGroup()->getID());
        }
        $lowestPrice = null;
        if(!empty($shippingMethods)) {
            foreach ($shippingMethods as $shippingMethod) {
                if ($lowestPrice === null || (float)$shippingMethod->fEndpreis < $lowestPrice) {
                    $lowestPrice = (float)$shippingMethod->fEndpreis;
                }
            }
        }
        return ($lowestPrice ?? 0.0) * $currency->getConversionFactor();
    }

    public function cartHasShippingMethod(): bool {
        return Frontend::getCart()->gibAnzahlPositionenExt([C_WARENKORBPOS_TYP_VERSANDPOS]) > 0;
    }

}