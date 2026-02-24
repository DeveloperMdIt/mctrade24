<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;


use JTL\Session\Frontend;
use JTL\Shop;

class Currency {

    public const AMAZON_SUPPORTED_CURRENCIES = [
        'AUD',
        'GBP',
        'DKK',
        'EUR',
        'HKD',
        'JPY',
        'NZD',
        'NOK',
        'ZAR',
        'SEK',
        'CHF',
        'USD'
    ];

    public const AMAZON_LEDGER_CURRENCIES = [
        'de' => 'EUR',
        'uk' => 'GBP',
        'us' => 'USD'
    ];

    public const AMAZON_MULTICURRENCY_REGIONS = [
        'de', 'uk'
    ];

    /**
     * @var Currency $instance
     */
    private static $instance;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * Mini in-request-caches.
     */
    private $shopCurrencyCodes;
    private $fallbackCurrency;

    private function __construct() {
        $this->config = Config::getInstance();
    }

    /**
     * Return single instance.
     * @return Currency
     */
    public static function getInstance(): Currency {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns a fallback currency.
     * Tries the default currency first, if it is not supported, either, returns the first matching currency or null if no matching currency is found.
     *
     * @return \JTL\Catalog\Currency|null
     */
    public function getFallbackCurrency(): ?\JTL\Catalog\Currency {
        if (null !== $this->fallbackCurrency) {
            return $this->fallbackCurrency;
        }
        $defaultCurrency = (new \JTL\Catalog\Currency())->getDefault();
        if ($this->isSupportedCurrency($defaultCurrency->getCode())) {
            $this->fallbackCurrency = $defaultCurrency;
            return $defaultCurrency;
        }
        // If the default currency is not supported, iterate over all currencies to find the first supported one.
        $allCurrencies = Frontend::getCurrencies();
        foreach ($allCurrencies as $currency) {
            if ($this->isSupportedCurrency($currency->getCode())) {
                $this->fallbackCurrency = $currency;
                return $currency;
            }
        }
        // No fallback currency found (this basically means we cannot offer Amazon Pay)
        return null;
    }

    /**
     * Checks if the given currency is supported by Amazon Pay.
     * @param $code
     * @return bool
     */
    public function isSupportedCurrency($code): bool {
        if ($code === null) {
            return false;
        }
        $code = mb_strtoupper($code);
        if ($this->config->isMultiCurrencyEnabled() && $this->isMultiCurrencyAllowed()) {
            // TODO LATER: FOR NOW, EXCLUDED CURRENCIES CAN NOT BE EDITED, SO THE SECOND PART OF THIS IF-STATEMENT IS ALWAYS TRUE
            return \in_array($code, self::AMAZON_SUPPORTED_CURRENCIES, true) && !\in_array($code, $this->config->getExcludedCurrencies(), true);
        }
        $ledgerCurrencyCode = $this->getLedgerCurrencyCode();
        return ($code === $ledgerCurrencyCode);
    }

    /**
     * Checks if the given currency is the ledger currency.
     * @param $code
     * @return bool
     */
    public function isLedgerCurrency($code): bool {
        return mb_strtoupper($code) === $this->getLedgerCurrencyCode();
    }

    /**
     * Checks if multicurrency is allowed.
     */
    public function isMultiCurrencyAllowed(): bool {
        return \in_array($this->config->getRegion(), self::AMAZON_MULTICURRENCY_REGIONS, true);
    }

    /**
     * Returns all shop currency codes.
     */
    public function getShopCurrencyCodes() {
        if (null !== $this->shopCurrencyCodes) {
            return $this->shopCurrencyCodes;
        }
        $res = Shop::Container()->getDB()->selectAll('twaehrung', [], []);
        if (empty($res)) {
            $this->shopCurrencyCodes = [];
            return [];
        }
        $res = array_map(function ($element) {
            return $element->cISO;
        }, $res);
        $this->shopCurrencyCodes = $res;
        return $res;
    }

    /**
     * Converts a float (such as created by gibGesamtsummeWaren) to a string as expected by AmazonPay
     * @param float $floatValue
     * @return string
     */
    public static function convertToAmazonString(float $floatValue): string {
        return number_format($floatValue, 2, '.', '');
    }

    /**
     * Returns the ledger currency code expected for the current configuration.
     */
    private function getLedgerCurrencyCode(): ?string {
        $region = $this->config->getRegion();
        if (empty($region) || !\array_key_exists($region, self::AMAZON_LEDGER_CURRENCIES)) {
            return null;
        }
        return self::AMAZON_LEDGER_CURRENCIES[$region];
    }

}