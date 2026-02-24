<?php

declare(strict_types=1);

namespace JTL\Shipping\Services;

use JTL\Cart\CartItem;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Lieferadresse;
use JTL\Country\Country;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Request;
use JTL\Helpers\Tax;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Plugin\Payment\LegacyMethod;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Session\Frontend;
use JTL\Settings\Option\Checkout;
use JTL\Settings\Option\Customer as CustomerOption;
use JTL\Settings\Settings;
use JTL\Shipping\DomainObjects\PaymentDTO;
use JTL\Shipping\DomainObjects\ShippingCartPositionDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shipping\DomainObjects\ShippingSurchargeDTO;
use JTL\Shipping\Helper\ProductDependentShippingCosts;
use JTL\Shipping\Helper\ShippingCalculationMethod;
use JTL\Shipping\Repositories\CacheRepository;
use JTL\Shipping\Repositories\DatabaseRepository;
use JTL\Shipping\Repositories\SessionRepository;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Traits\FloatingPointTrait;
use RuntimeException;

/**
 * Class ShippingService
 * @package JTL\Shipping
 * @since 5.5.0
 */
class ShippingService
{
    use FloatingPointTrait;

    private ?LanguageHelper $languageHelper = null;

    /**
     * @var LanguageModel[]
     */
    private ?array $availableLangs = null;

    private ?CountryServiceInterface $countryService = null;

    /**
     * @var array<int, array<int, array<int, ShippingDTO>>>
     * [customerGroupID][shippingClassID] => ShippingDTO[]
     */
    private array $methodsWithFreeShipping = [];

    /**
     * @var array<string, PaymentDTO[]>
     */
    private array $paymentMethods = [];

    /**
     * @var array<string, ShippingDTO[]>
     */
    private array $possibleShippingMethods = [];

    /**
     * @var array<string, bool>
     */
    private array $paymentMethodValidation = [];

    /**
     * @description Properties that are used to calculate the checksum of the cart, in order to detect changes that
     *  are relevant for shipping calculation. The keys are mapped directly with their values to the CartItem properties
     *  The array is used inside the session repository in order to retrieve and store possible shipping methods:
     *  [countryISO + zipCode + checksum] => ShippingDTO[]
     */
    private const CART_CHECKSUM_SHIPPING_PROPERTIES = [
        'id'           => 'kArtikel',
        'qty'          => 'nAnzahl',
        'netUnitPrice' => 'fPreisEinzelNetto',
        'netPrice'     => 'fPreis',
        'weight'       => 'fGesamtgewicht',
    ];

    private string $taxCalculationMethod = '';

    public function __construct(
        protected readonly DatabaseRepository $database = new DatabaseRepository(),
        protected readonly CacheRepository $cache = new CacheRepository(),
        protected readonly SessionRepository $session = new SessionRepository(),
    ) {
    }

    public function getDeliveryAddress(Customer|null $customer = null): Lieferadresse
    {
        // Build address from user input
        $deliveryAddress = $this->getDeliveryAddressFromUI();
        if ($deliveryAddress !== null) {
            $this->session->setDeliveryAddress($deliveryAddress);
            return $deliveryAddress;
        }
        // Get address from session data
        $deliveryAddress = $this->session->getDeliveryAddress();
        if (empty($deliveryAddress->cLand) === false) {
            return $deliveryAddress;
        }
        // Get address from database
        $deliveryAddress = $this->database->getPreferredDeliveryAddress($customer->kKunde ?? 0);
        if ($deliveryAddress !== null) {
            $this->session->setDeliveryAddress($deliveryAddress);
            return $deliveryAddress;
        }
        // Build minimal address with customer data or default values
        $result        = new Lieferadresse();
        $result->cLand = $customer->cLand
            ?? Settings::stringValue(CustomerOption::DELIVERY_ADDRESS_COUNTRY_PROMPT);
        $result->cPLZ  = $customer->cPLZ
            ?? '';
        $this->session->setDeliveryAddress($result);

        return $result;
    }

    public function setTaxRateByDeliveryCountry(string $deliveryCountry): void
    {
        if ((string)($_SESSION['cLieferlandISO'] ?? '') !== $deliveryCountry) {
            Tax::setTaxRates($deliveryCountry);
        }
    }

    public function getLanguageHelper(): LanguageHelper
    {
        return $this->languageHelper ?? $this->setLanguageHelper();
    }

    public function setLanguageHelper(?LanguageHelper $languageHelper = null): LanguageHelper
    {
        $this->languageHelper ??= $languageHelper ?? Shop::Lang();
        return $this->languageHelper;
    }

    public function getCountryService(): CountryServiceInterface
    {
        return $this->countryService ?? $this->setCountryService();
    }

    public function setCountryService(?CountryServiceInterface $countryService = null): CountryServiceInterface
    {
        $this->countryService ??= $countryService ?? Shop::Container()->getCountryService();
        return $this->countryService;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getShippingMethods()
     * @return ShippingDTO[]
     */
    public function getAllShippingMethods(): array
    {
        return $this->database->getAllShippingMethods(
            $this->getAvailableLanguages()
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getPossibleShippingMethods()
     * @param CartItem[] $cartItems
     * @return ShippingDTO[]
     */
    public function getPossibleShippingMethods(
        Customer $customer,
        CustomerGroup $customerGroup,
        string $deliveryCountryCode,
        Currency $currency,
        string $deliveryZipCode = '',
        array $cartItems = [],
    ): array {
        $checksum        = $this->getCartChecksum($cartItems, $deliveryCountryCode);
        $possibleMethods = $this->session->getPossibleMethods(
            $deliveryCountryCode,
            $deliveryZipCode,
            $checksum,
        );
        if (empty($possibleMethods) === false) {
            return $this->applyShippingFreeCoupon($possibleMethods, $currency);
        }
        $possibleMethods = $this->getMethodsFromCacheOrDB(
            $deliveryCountryCode,
            $customerGroup->getID(),
        );
        if (empty($possibleMethods)) {
            return [];
        }
        $possibleMethods = $this->filterByCountriesAndShippingClasses(
            $possibleMethods,
            [$deliveryCountryCode],
            $cartItems,
            $customerGroup->getID(),
        );
        $possibleMethods = $this->setOptionalDataInShippingMethods(
            $possibleMethods,
            $deliveryCountryCode,
            $deliveryZipCode,
            $customer,
            $customerGroup,
            $currency,
            $cartItems
        );
        $possibleMethods = $this->filterOutPriciestMethods($possibleMethods);
        $this->session->setPossibleMethods(
            $possibleMethods,
            $deliveryCountryCode,
            $deliveryZipCode,
            $checksum,
        );

        return $this->applyShippingFreeCoupon($possibleMethods, $currency);
    }

    /**
     * @param ShippingDTO[] $shippingMethods
     * @return ShippingDTO[]
     */
    public function sortMethodsByPrice(array $shippingMethods): array
    {
        \usort(
            $shippingMethods,
            static function (ShippingDTO $methodA, ShippingDTO $methodB): int {
                return $methodA->finalNetCost <=> $methodB->finalNetCost;
            }
        );

        return $shippingMethods;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getDeliveryText()
     */
    public function getDeliveryText(
        int $minDeliveryDays,
        int $maxDeliveryDays,
        string $translationOffset
    ): string {
        if (\stripos($translationOffset, 'simple') === false) {
            return \str_replace(
                ['#MINDELIVERYTIME#', '#MAXDELIVERYTIME#'],
                [(string)$minDeliveryDays, (string)$maxDeliveryDays],
                $this->getLanguageHelper()->get($translationOffset)
            );
        }

        return \str_replace(
            '#DELIVERYTIME#',
            (string)$minDeliveryDays,
            $this->getLanguageHelper()->get($translationOffset)
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\filter()
     * @param CartItem[] $cartItems
     * @return ShippingDTO[]
     */
    public function getPossibleFreeShippingMethods(
        Customer $customer,
        CustomerGroup $customerGroup,
        Currency $currency,
        string $country,
        array $cartItems = [],
        string $zipCode = '',
        float $maxThreshold = \PHP_FLOAT_MAX
    ): array {
        $freeShippingMethods = \array_filter(
            $this->getPossibleShippingMethods($customer, $customerGroup, $country, $currency, $zipCode, $cartItems),
            function ($method) use ($maxThreshold): bool {
                return $this->isZero($method->finalNetCost)
                    || (
                        $method->freeShippingMinAmount > 0
                        && $method->freeShippingMinAmount <= $maxThreshold
                    );
            }
        );

        \usort(
            $freeShippingMethods,
            static fn(ShippingDTO $methodA, ShippingDTO $methodB) =>
                $methodA->freeShippingMinAmount <=> $methodB->freeShippingMinAmount
        );

        return \array_values($freeShippingMethods);
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getFreeShippingCountries()
     * @param object{Preise: object{fVK: array<int, float>}, kVersandklasse: int, FunktionsAttribute: array} $product
     * @return string[]
     * @todo Make $product more explicit, setting the param to Artikel as soon as ShippingMethods.php gets removed.
     */
    public function getFreeShippingCountriesByProduct(
        int $customerGroupID,
        object $product,
    ): array {
        $netPrice        = $product->Preise->fVK[1] ?? 0.00;
        $grossPrice      = $product->Preise->fVK[0] ?? 0.00;
        $shippingClassID = $product->kVersandklasse ?? 0;
        $customCosts     = $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]
            ?? $product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
            ?? '';
        if (!isset($this->methodsWithFreeShipping[$customerGroupID][$shippingClassID])) {
            if (!isset($this->methodsWithFreeShipping[$customerGroupID])) {
                $this->methodsWithFreeShipping[$customerGroupID] = [];
            }
            $this->methodsWithFreeShipping[$customerGroupID][$shippingClassID] = $this->database
                ->getFreeShippingMethods(
                    $customerGroupID,
                    $shippingClassID
                );
        }
        $shippingFreeCountries = [];
        foreach ($this->methodsWithFreeShipping[$customerGroupID][$shippingClassID] as $shippingMethod) {
            if (
                $shippingMethod->freeShippingMinAmount >= (
                    $shippingMethod->includeTaxes
                    ? $grossPrice
                    : $netPrice
                )
            ) {
                continue;
            }
            foreach ($shippingMethod->countries as $country) {
                if (
                    (\is_string($customCosts) && \str_contains($customCosts, $country))
                    || \in_array($country, $shippingFreeCountries, true)
                ) {
                    continue;
                }

                $shippingFreeCountries[] = $country;
            }
        }

        return $shippingFreeCountries;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\normalerArtikelversand()
     * @param string[]   $countries
     * @param CartItem[] $cartItems
     * @description Returns true when all $cartItems have custom shipping costs.
     */
    public function cartIsDependent(array $cartItems = [], array $countries = ['']): bool
    {
        $shippingTypeCount = $this->computeCostTypes($cartItems, $countries);
        return $shippingTypeCount->normalShippingItems === 0 && $shippingTypeCount->dependentShippingItems > 0;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\hasSpecificShippingcosts()
     * @param string[]   $countries
     * @param CartItem[] $cartItems
     * @description Returns true when there is at least one product with custom shipping costs.
     */
    public function cartIsMixed(array $cartItems = [], array $countries = ['']): bool
    {
        $shippingTypeCount = $this->computeCostTypes($cartItems, $countries);
        return $shippingTypeCount->normalShippingItems > 0 && $shippingTypeCount->dependentShippingItems > 0;
    }

    /**
     * @param PaymentDTO[] $paymentMethods
     */
    public function filterPaymentMethodByID(array $paymentMethods, int $paymentMethodID): ?PaymentDTO
    {
        $filteredMethods = \array_values(
            \array_filter(
                $paymentMethods,
                static function (PaymentDTO $paymentMethod) use ($paymentMethodID): bool {
                    return $paymentMethod->id === $paymentMethodID;
                }
            )
        );

        return $filteredMethods[0] ?? null;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getPaymentMethods()
     * @return PaymentDTO[]
     * @todo Cache entry must be unset whenever associated shipping methods got modified or payment methods got deleted
     */
    public function getPossiblePaymentMethods(int $shippingMethodID, int $customerGroupID): array
    {
        $allowdMethods = $this->cache->getPaymentMethods(
            $shippingMethodID,
            $customerGroupID
        );
        if ($allowdMethods !== []) {
            return $allowdMethods;
        }
        $paymentMethodsOffset = \md5($shippingMethodID . $customerGroupID);
        $allowdMethods        = $this->paymentMethods[$paymentMethodsOffset]
            ?? $this->database->getPaymentMethods(
                $shippingMethodID,
                $customerGroupID,
            );
        if ($allowdMethods !== []) {
            $this->cache->setPaymentMethods(
                $shippingMethodID,
                $customerGroupID,
                $allowdMethods
            );
            $this->paymentMethods[$paymentMethodsOffset] = $allowdMethods;
        }

        return $allowdMethods;
    }

    public function getVatNote(bool $isMerchant): string
    {
        return $isMerchant
            ? ' '
                . $this->getLanguageHelper()->get('plus', 'productDetails')
                . ' '
                . $this->getLanguageHelper()->get('vat', 'productDetails')
            : '';
    }

    /**
     * @former JTL\Catalog\Product\Artikel\getFavourableShipping()
     * @comment Replacing cache with session could give +- 120ms performance boost in some cases.
     */
    public function getFavourableShippingForProduct(
        Artikel $product,
        string $country,
        Customer $customer,
        CustomerGroup $customerGroup,
        Currency $currency,
        string $zipCode = '',
    ): ?ShippingDTO {
        $cartItem        = $this->cartItemFromProduct($product);
        $possibleMethods = $this->getMethodsFromCacheOrDB(
            $country,
            $customerGroup->getID(),
        );
        if (\count($possibleMethods) === 0) {
            return null;
        }
        $possibleMethods = $this->filterByCountriesAndShippingClasses(
            $possibleMethods,
            [$country],
            [$cartItem],
            $customerGroup->getID(),
        );

        return $this->getFavourableShippingMethod(
            $this->setOptionalDataInShippingMethods(
                $possibleMethods,
                $country,
                $zipCode,
                $customer,
                $customerGroup,
                $currency,
                [$cartItem],
            )
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\gibHinzukommendeArtikelAbhaengigeVersandkosten()
     * @param ShippingDTO[] $dependentShippingMethods
     * @param CartItem[] $cartItems
     */
    public function simulateCustomShippingCosts(
        string $iso,
        array $dependentShippingMethods,
        array $cartItems,
        Artikel $product,
        float $productAmount,
        bool $isMerchant,
        Currency $currency,
    ): ?ShippingCartPositionDTO {
        if (
            $product->kArtikel === null
            || $product->FunktionsAttribute === null
            || $iso === ''
        ) {
            return null;
        }

        if (\str_contains($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT] ?? '', $iso)) {
            $productAmount += \array_sum(
                \array_map(
                    static fn($position) => (float)$position->nAnzahl,
                    \array_filter(
                        $cartItems,
                        static fn($position) => (int)$position->kArtikel === $product->kArtikel
                    )
                )
            );
        }

        $dependentMethod = \array_reduce(
            $dependentShippingMethods,
            static function (?ShippingDTO $carry, ShippingDTO $method): ?ShippingDTO {
                return $method->isDependent ? $method : $carry;
            },
        );
        if ($dependentMethod instanceof ShippingDTO === false) {
            return null;
        }

        return $this->getCustomShippingCostsByProduct(
            $iso,
            $product,
            $productAmount,
            $dependentMethod,
            $isMerchant,
            $currency,
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\pruefeArtikelabhaengigeVersandkosten()
     * @comment Cant take care of possible forgotten FunktionsAttribute. Only one value should be set at a time.
     */
    public function getCustomShippingCostType(Artikel $product): ProductDependentShippingCosts
    {
        $hookReturn = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_PRUEFEARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'bHookReturn' => &$hookReturn
        ]);

        if (
            (bool)$hookReturn === true
            || $product->FunktionsAttribute === null
        ) {
            return ProductDependentShippingCosts::NONE;
        }
        return match (false) {
            empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN])            =>
            ProductDependentShippingCosts::FIX,
            empty($product->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]) =>
            ProductDependentShippingCosts::BULK,
            default                                                                     =>
            ProductDependentShippingCosts::NONE,
        };
    }

    /**
     * @former JTL\Helpers\ShippingMethod\gibArtikelabhaengigeVersandkosten()
     */
    public function getCustomShippingCostsByProduct(
        string $country,
        Artikel $product,
        float $productQty,
        ShippingDTO $dependentShippingMethod,
        bool $isMerchant,
        Currency $currency,
    ): ?ShippingCartPositionDTO {
        $customCostType = $this->getCustomShippingCostType($product);
        if (
            $customCostType === ProductDependentShippingCosts::NONE
            || $dependentShippingMethod->isDependent === false
        ) {
            return null;
        }

        return $this->getCustomShippingCosts(
            $country,
            $product,
            $productQty,
            $isMerchant,
            $currency,
            $dependentShippingMethod,
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\gibArtikelabhaengigeVersandkostenImWK()
     * @param CartItem[] $cartItems
     * @return ShippingCartPositionDTO[]
     */
    public function getCustomShippingCostsByCart(
        string $country,
        CustomerGroup $customerGroup,
        Currency $currency,
        array $cartItems = [],
        ShippingDTO|null $dependentShippingMethod = null,
    ): array {
        $result                   = [];
        $cartItemsWithCustomCosts = \array_filter(
            $cartItems,
            function (CartItem $item) use ($country): bool {
                return (
                    (int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                    && $item->Artikel !== null
                    && $this->getCustomShippingCostType($item->Artikel) !== ProductDependentShippingCosts::NONE
                    && $item->Artikel->isUsedForShippingCostCalculation($country) === false
                );
            }
        );

        if (\count($cartItemsWithCustomCosts) === 0) {
            return [];
        }

        $dependentShippingMethod ??= $this->getDependentShippingMethod(
            $country,
            $cartItemsWithCustomCosts,
            $customerGroup->getID()
        );
        foreach ($cartItemsWithCustomCosts as $item) {
            if ($item->Artikel === null) {
                continue;
            }
            $shippingItem = $this->getCustomShippingCosts(
                $country,
                $item->Artikel,
                (float)$item->nAnzahl,
                $customerGroup->isMerchant(),
                $currency,
                $dependentShippingMethod,
            );

            if ($shippingItem !== null) {
                $result[] = $shippingItem;
            }
        }

        return $result;
    }

    public function getTaxRate(string $country = '', ?int $taxClassID = null): float
    {
        if ($taxClassID === null) {
            throw new RuntimeException(
                'Could not get tax rate without a valid tax class ID'
            );
        }
        $taxRate = $this->session->getTaxRateByTaxClassID($taxClassID);
        if ($taxRate === null) {
            $taxRate = Tax::getSalesTax(
                $taxClassID,
                $country
            );
            if (\is_numeric($taxRate) === false) {
                throw new RuntimeException(
                    'Could not get tax rate for the tax class with ID ' . $taxClassID
                );
            }
            $taxRate = (float)$taxRate;
        }

        return $taxRate;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getShippingClasses()
     * @param CartItem[]|null $cartItems
     * @return int[]
     */
    public function getShippingClasses(array $cartItems = []): array
    {
        $result = [];
        foreach ($cartItems as $item) {
            if (
                (int)$item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $item->kVersandklasse > 0
            ) {
                $result[$item->kVersandklasse] = $item->kVersandklasse;
            }
        }
        $result = \array_values($result);
        \sort($result);

        return $result;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getAdditionalFees()
     * @param CartItem[] $cartItems
     */
    public function getShippingSurcharge(
        ShippingDTO $shippingMethod,
        string $country,
        string $zipCode,
        bool $isMerchant,
        Currency $currency,
        array $cartItems = [],
    ): ?ShippingSurchargeDTO {
        if ($zipCode === '') {
            return null;
        }
        $shippingSurcharge = $this->cache->getShippingSurcharge(
            $shippingMethod->id,
            $country,
            $zipCode,
        );
        if ($shippingSurcharge !== null) {
            return $shippingSurcharge;
        }
        $shippingSurcharge = $this->database->getSurchargeForShippingMethod(
            $shippingMethod->id,
            $country,
            $zipCode,
            $this->getAvailableLanguages(),
        );
        if ($shippingSurcharge === null) {
            return null;
        }
        $surchargeNetPrice = $shippingMethod->includeTaxes
            ? $this->calculateNetPrice($shippingSurcharge->netSurcharge, $cartItems, $country)
            : $shippingSurcharge->netSurcharge;

        $shippingSurcharge->netSurcharge       = $surchargeNetPrice;
        $shippingSurcharge->surchargeLocalized = Preise::getLocalizedPriceString(
            $isMerchant
                ? $surchargeNetPrice
                : $this->calculateGrossPrice(
                    $surchargeNetPrice,
                    $cartItems,
                    $country,
                ),
            $currency
        );

        $shippingSurcharge = ShippingSurchargeDTO::fromObject($shippingSurcharge);
        $this->cache->setShippingSurcharge(
            $shippingMethod->id,
            $country,
            $zipCode,
            $shippingSurcharge,
        );

        return $shippingSurcharge;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\calculateShippingFees()
     * @param CartItem[] $cartItems
     */
    public function calcCostForShippingMethod(
        ShippingDTO $shippingMethod,
        string $iso,
        Customer $customer,
        array $cartItems = [],
        ?Artikel $product = null,
    ): float {
        if ($product !== null) {
            $cartItems[] = $this->cartItemFromProduct(
                $product,
                $customer->getGroupID(),
                $customer->getID(),
            );
        }
        $cartItems = $this->filterCartItemsByShippingMethod(
            $shippingMethod,
            $cartItems,
            $iso
        );
        $netPrice  = $this->getCostsDependingOnCalcMethod(
            $shippingMethod,
            $iso,
            $cartItems,
        );
        if ($netPrice === -1.0) {
            return -1.0;
        }
        \executeHook(\HOOK_CALCULATESHIPPINGFEES, [
            'price'             => &$netPrice,
            'shippingMethod'    => $shippingMethod->toVersandart($iso),
            'iso'               => $iso,
            'additionalProduct' => $cartItems[0]->Artikel ?? null,
            'product'           => $product,
        ]);
        \executeHook(\HOOK_CALCULATE_SHIPPING_COSTS, [
            'price'              => &$netPrice,
            'shippingMethod'     => $shippingMethod,
            'deliveryCountryISO' => $iso,
            'product'            => $product,
            'cartItems'          => &$cartItems,
        ]);
        if ($shippingMethod->maxPrice > 0) {
            $netPrice = \min(
                $netPrice,
                $shippingMethod->includeTaxes
                    ? $this->calculateNetPrice($shippingMethod->maxPrice, $cartItems, $iso)
                    : $shippingMethod->maxPrice
            );
        }
        if (
            $shippingMethod->freeShippingMinAmount > 0
            && $this->getItemsTotalValue(
                [\C_WARENKORBPOS_TYP_ARTIKEL],
                $cartItems,
                $shippingMethod->includeTaxes,
                $iso,
            ) >= $shippingMethod->freeShippingMinAmount
        ) {
            $netPrice = 0;
        }
        // Add surcharges even if method qualifies for free shipping or if maxPrice has been applied.
        $netPrice += $shippingMethod->shippingSurcharge->netSurcharge ?? 0.0;
        \executeHook(\HOOK_TOOLSGLOBAL_INC_BERECHNEVERSANDPREIS, [
            'fPreis'         => &$netPrice,
            'versandart'     => $shippingMethod->toVersandart($iso),
            'cISO'           => $iso,
            'oZusatzArtikel' => $cartItems[0]->Artikel ?? null,
            'Artikel'        => $product,
        ]);
        \executeHook(\HOOK_CALCULATE_SHIPPING_COSTS_END, [
            'price'              => &$netPrice,
            'shippingMethod'     => $shippingMethod,
            'deliveryCountryISO' => $iso,
            'product'            => $product,
            'cartItems'          => &$cartItems,
        ]);

        return $netPrice;
    }

    public function getProductPrice(
        Artikel $product,
        ShippingDTO $shippingMethod,
        int $customerID,
        int $customerGroupID,
    ): float {
        if ($product->Preise === null) {
            $product->holPreise(
                $customerGroupID,
                $product,
                $customerID,
            );
        }

        return $shippingMethod->includeTaxes
            ? $product->Preise->fVK[1] ?? 0.00
            : $product->Preise->fVK[0] ?? 0.00;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getLowestShippingFees()
     * @comment Only used during export. Called in includes/src/Export/Product.php and in our google shopping plugin.
     */
    public function getLowestShippingFeesForProduct(
        string $iso,
        Artikel $product,
        bool $allowCash,
        Customer $customer,
        Currency $currency,
    ): float {
        $fee            = null;
        $cheapestMethod = null;
        $isDependent    = $this->getCustomShippingCostType($product) !== ProductDependentShippingCosts::NONE;
        $methods        = $this->database->getShippingMethods(
            $customer->getGroupID(),
            $this->getAvailableLanguages(),
            $iso,
        );
        $methods        = $this->filterByCountriesAndShippingClasses(
            $methods,
            [$iso],
            [$this->cartItemFromProduct($product)],
            $customer->getGroupID(),
        );
        foreach ($methods as $method) {
            if ($isDependent !== $method->isDependent) {
                continue;
            }
            if ($method->exclFromCheapestCalc) {
                continue;
            }
            if (
                $allowCash === false
                && $method->acceptsCashPayment
            ) {
                continue;
            }

            $shippingFee = $this->calcCostForShippingMethod(
                $method,
                $iso,
                $customer,
                [],
                $product,
            );

            if ($shippingFee !== -1.0 && ($fee === null || $shippingFee < $fee)) {
                $fee            = $shippingFee;
                $cheapestMethod = $method;
            }
            if ($this->isZero($shippingFee)) {
                break;
            }
        }
        if ($fee === null || $cheapestMethod === null) {
            return -1.0;
        }
        if ($isDependent) {
            $fee += $this->getCustomShippingCostsByProduct(
                $iso,
                $product,
                1.0,
                $cheapestMethod,
                (new CustomerGroup($customer->getGroupID()))->isMerchant(),
                $currency,
            )->netPrice ?? 0.0;
        }

        return $fee;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getDeliverytimeEstimationText()
     * @comment Would be nice to remove these constants or replace them with settings.
     */
    public function getDeliverytimeEstimationText(
        int $minDeliveryDays,
        int $maxDeliveryDays,
        int $daysToWeeksLimit = \DELIVERY_TIME_DAYS_TO_WEEKS_LIMIT,
        int $daysToMonthsLimit = \DELIVERY_TIME_DAYS_TO_MONTHS_LIMIT,
        int $daysPerWeek = \DELIVERY_TIME_DAYS_PER_WEEK,
        int $daysPerMonth = \DELIVERY_TIME_DAYS_PER_MONTH,
    ): string {
        switch (true) {
            case ($maxDeliveryDays < $daysToWeeksLimit):
                $minDelivery = $minDeliveryDays;
                $maxDelivery = $maxDeliveryDays;
                $languageVar = $minDeliveryDays === $maxDeliveryDays
                    ? 'deliverytimeEstimationSimple'
                    : 'deliverytimeEstimation';
                break;
            case ($maxDeliveryDays < $daysToMonthsLimit):
                $minDelivery = (int)\ceil($minDeliveryDays / $daysPerWeek);
                $maxDelivery = (int)\ceil($maxDeliveryDays / $daysPerWeek);
                $languageVar = $minDelivery === $maxDelivery
                    ? 'deliverytimeEstimationSimpleWeeks'
                    : 'deliverytimeEstimationWeeks';
                break;
            default:
                $minDelivery = (int)\ceil($minDeliveryDays / $daysPerMonth);
                $maxDelivery = (int)\ceil($maxDeliveryDays / $daysPerMonth);
                $languageVar = $minDelivery === $maxDelivery
                    ? 'deliverytimeEstimationSimpleMonths'
                    : 'deliverytimeEstimationMonths';
        }

        $deliveryText = $this->getDeliveryText($minDelivery, $maxDelivery, $languageVar);

        \executeHook(\HOOK_GET_DELIVERY_TIME_ESTIMATION_TEXT, [
            'min'  => $minDeliveryDays,
            'max'  => $maxDeliveryDays,
            'text' => &$deliveryText
        ]);

        return $deliveryText;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getShippingFreeString()
     * @param CartItem[] $cartItems
     */
    public function getShippingFreeString(
        ShippingDTO $method,
        bool $isMerchant,
        Currency $currency,
        array $cartItems = [],
        string $country = ''
    ): string {
        $returnEmptyString = (
            $this->session->isset('oVersandfreiKupon')
            && (
                $this->session->isset('Warenkorb') === false
                && $this->session->isset('Steuerland') === false
            )
        );
        if (
            $returnEmptyString
            || (
                $method->freeShippingMinAmount <= 0
                && $method->finalNetCost > 0
            )
        ) {
            return '';
        }

        $shippingFreeDifference = $this->getShippingFreeDifference(
            $method,
            $cartItems,
            $country,
            $isMerchant,
        );
        if ($shippingFreeDifference <= 0) {
            return \sprintf(
                $this->getLanguageHelper()->get('noShippingCostsReached', 'basket'),
                $method->localizedNames[$this->getLanguageHelper()->getLanguageCode()] ?? '',
                $this->getShippingFreeCountriesString($method)
            );
        }

        return \sprintf(
            $this->getLanguageHelper()->get('noShippingCostsAt', 'basket'),
            Preise::getLocalizedPriceString($shippingFreeDifference, $currency)
            . ($isMerchant
                ? ' (' . $this->getLanguageHelper()->get('net') . ')'
                : ''),
            $method->localizedNames[$this->getLanguageHelper()->getLanguageCode()] ?? '',
            $this->getShippingFreeCountriesString($method)
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getShippingFreeDifference()
     * @param CartItem[] $cartItems
     */
    public function getShippingFreeDifference(
        ShippingDTO $method,
        array $cartItems = [],
        string $country = '',
        bool $isMerchant = false,
    ): float {
        $shippingFreeAmount = $method->freeShippingMinAmount;
        $itemPriceToGross   = false;
        if ($isMerchant && $method->includeTaxes) {
            $shippingFreeAmount = $this->calculateNetPrice($method->freeShippingMinAmount, $cartItems, $country);
        } elseif ($isMerchant === false) {
            $itemPriceToGross = true;
            if ($method->includeTaxes === false) {
                $shippingFreeAmount = $this->calculateGrossPrice($method->freeShippingMinAmount, $cartItems, $country);
            }
        }

        return $shippingFreeAmount - $this->getItemsTotalValue(
            [\C_WARENKORBPOS_TYP_ARTIKEL],
            $cartItems,
            $itemPriceToGross,
            $country,
        );
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getShippingFreeCountriesString()
     */
    public function getShippingFreeCountriesString(ShippingDTO $shippingMethod): string
    {
        if ($shippingMethod->freeShippingMinAmount <= 0) {
            return '';
        }

        $langID                = $this->getLanguageHelper()->getLanguageID();
        $freeShippingCountries = $this->cache->getFreeShippingCountries(
            $shippingMethod,
            $langID
        )
            ?: $this->cache->setFreeShippingCountries(
                $shippingMethod,
                $langID
            );

        if ($freeShippingCountries === []) {
            $freeShippingCountries = $this->getCountryService()
                ->getFilteredCountryList($shippingMethod->countries)
                ->map(
                    static function ($country) use ($langID): string {
                        return $country instanceof Country
                            ? $country->getName($langID)
                            : '';
                    }
                )->all();
        }

        return \implode(', ', $freeShippingCountries ?? []);
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getFreeShippingMinimum()
     * @param CartItem[] $cartItems
     */
    public function getFreeShippingMethod(
        Customer $customer,
        CustomerGroup $customerGroup,
        Currency $currency,
        string $countryISOCode = '',
        array $cartItems = [],
        string $zipCode = '',
    ): ?ShippingDTO {
        return $this->getPossibleFreeShippingMethods(
            $customer,
            $customerGroup,
            $currency,
            $countryISOCode,
            $cartItems,
            $zipCode,
        )[0] ?? null;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getPossibleShippingCountries()
     * @param string[]   $allowedCountries
     * @param CartItem[] $cartItems
     * @return Country[]
     */
    public function getPossibleShippingCountries(
        array $allowedCountries = [],
        int $customerGroupID = 0,
        array $cartItems = [],
    ): array {
        $possibleMethods   = $this->filterByCountriesAndShippingClasses(
            $this->database->getShippingMethods(
                $customerGroupID,
                $this->getAvailableLanguages(),
                '',
                false,
                false,
            ),
            $allowedCountries,
            $cartItems,
            $customerGroupID,
        );
        $shippingCountries = [];
        foreach ($possibleMethods as $shippingMethod) {
            foreach ($shippingMethod->countries as $country) {
                $shippingCountries[] = $country;
            }
        }
        $shippingCountries = \array_unique($shippingCountries);
        /** @var Country[] $countries */
        $countries = $this->getCountryService()->getFilteredCountryList($shippingCountries)->toArray();
        \executeHook(\HOOK_TOOLSGLOBAL_INC_GIBBELIEFERBARELAENDER, [
            'oLaender_arr' => &$countries
        ]);

        return $countries;
    }

    /**
     * @former JTL\Helpers\ShippingMethod\getPossiblePackagings()
     * @param CartItem[] $cartItems
     * @return array<int, object{kVerpackung: int, kSteuerklasse: int, cName: string, cKundengruppe: string,
     *     fBrutto: float, fMindestbestellwert: float, fKostenfrei: float, nAktiv: int, kVerpackungSprache: int,
     *     cISOSprache: string, cBeschreibung: string, fBruttoLocalized: string, nKostenfrei: int,
     *     fBruttoLocalized: float, nKostenfrei: 1|0}>
     */
    public function getPossiblePackagings(int $customerGroupID, Currency $currency, array $cartItems = []): array
    {
        $result  = [];
        $cartSum = $this->getItemsTotalValue([\C_WARENKORBPOS_TYP_ARTIKEL], $cartItems, false);
        foreach (
            $this->database->getPackagings(
                $this->getLanguageHelper()->getLanguageCode(),
                $customerGroupID,
                $cartSum,
            ) as $packaging
        ) {
            $result[] = (object)\array_merge((array)$packaging, [
                'fBruttoLocalized' => Preise::getLocalizedPriceString(
                    $packaging->fBrutto,
                    $currency,
                ),
                'nKostenfrei'      => (
                    $cartSum >= $packaging->fKostenfrei
                    && $packaging->fBrutto > 0
                    && $packaging->fKostenfrei > 0
                )
                    ? 1
                    : 0,
            ]);
        }

        return $result;
    }

    /**
     * @param ShippingDTO[] $shippingMethods
     * @return ShippingDTO[]
     */
    public function filterShippingMethods(
        array $shippingMethods,
        int $paymentMethodID,
        int $customerGroupID,
    ): array {
        if ($paymentMethodID === 0) {
            return $shippingMethods;
        }
        $result = [];
        foreach ($shippingMethods as $shippingMethod) {
            foreach ($this->getPossiblePaymentMethods($shippingMethod->id, $customerGroupID) as $paymentMethod) {
                if ($paymentMethod->id === $paymentMethodID) {
                    $result[] = $shippingMethod;
                    break;
                }
            }
        }

        return $result;
    }

    public function getPaymentMethodID(): int
    {
        return $this->session->getPaymentMethod()->kZahlungsart ?? 0;
    }

    /**
     * @param CartItem[] $cartItems
     */
    public function calculateNetPrice(
        float $grossPrice,
        array $cartItems = [],
        string $country = '',
        ?int $taxClassID = null,
    ): float {
        return $grossPrice / (
            100 + $this->getTaxRate(
                $country,
                $taxClassID
                    ?? $this->getTaxRateIDs($this->getTaxCalculationMethod(), $cartItems, $country)[0]->taxRateID
                    ?? null
            )) * 100;
    }

    /**
     * @param CartItem[] $cartItems
     */
    public function calculateGrossPrice(
        float $netPrice,
        array $cartItems = [],
        string $country = '',
        ?int $taxClassID = null,
    ): float {
        return Tax::getGross(
            $netPrice,
            $this->getTaxRate(
                $country,
                $taxClassID
                    ?? $this->getTaxRateIDs($this->getTaxCalculationMethod(), $cartItems, $country)[0]->taxRateID
                    ?? null
            )
        );
    }

    /**
     * @param CartItem[] $cartItemsWithCustomCosts
     */
    public function getDependentShippingMethod(
        string $country,
        array $cartItemsWithCustomCosts,
        int $customerGroupID,
    ): ?ShippingDTO {
        $hash                     = $this->getCartChecksum($cartItemsWithCustomCosts, $country);
        $dependentShippingMethods = $this->session->getPossibleDependentMethods(
            $country,
            $hash,
        );
        if (\count($dependentShippingMethods) === 0) {
            $dependentShippingMethods = \array_filter(
                $this->getMethodsFromCacheOrDB(
                    $country,
                    $customerGroupID,
                ),
                static function (ShippingDTO $method): bool {
                    return $method->isDependent;
                },
            );
            $dependentShippingMethods = $this->filterByCountriesAndShippingClasses(
                $dependentShippingMethods,
                [$country],
                $cartItemsWithCustomCosts,
                $customerGroupID,
            );
            $this->session->setPossibleDependentMethods(
                $dependentShippingMethods,
                $country,
                $hash,
            );
        }

        $dependentShippingMethod = \array_reduce(
            $dependentShippingMethods,
            static function (?ShippingDTO $carry, ShippingDTO $method): ?ShippingDTO {
                return $method->isDependent ? $method : $carry;
            },
        );

        return $dependentShippingMethod instanceof ShippingDTO
            ? $dependentShippingMethod
            : null;
    }

    /**
     * @param CartItem[] $cartItems
     * @return array<int, object{taxRateID: int, proportion: float}>
     */
    public function getTaxRateIDs(
        string $shippingTaxConfigValue = '',
        array $cartItems = [],
        string $country = ''
    ): array {
        if ($shippingTaxConfigValue === '') {
            $shippingTaxConfigValue = $this->getTaxCalculationMethod();
        }

        return match ($shippingTaxConfigValue) {
            'US' => [
                (object)[
                    'taxRateID'  => $this->getPredominantTaxRate($cartItems),
                    'proportion' => 100.00,
                ]
            ],
            'HS' => [
                (object)[
                    'taxRateID'  => $this->getHighestTaxRate($cartItems, $country),
                    'proportion' => 100.00,
                ]
            ],
            'PS' => $this->getProportionalTaxRates($cartItems),
            default => [],
        };
    }

    /**
     * @param ShippingDTO[]|null $shippingMethods
     */
    public function getFirstShippingMethod(
        array $shippingMethods,
        int $customerGroupID,
        int $paymentMethodID,
    ): ?ShippingDTO {
        return $this->filterShippingMethods(
            $shippingMethods,
            $paymentMethodID,
            $customerGroupID,
        )[0] ?? null;
    }

    /**
     * @param int[]      $types
     * @param CartItem[] $cartItems
     */
    private function getItemsTotalValue(
        array $types,
        array $cartItems,
        bool $toGross,
        string $iso = '',
    ): float {
        $total = 0.0;
        foreach ($cartItems as $item) {
            /** @var CartItem $item */
            if (
                \in_array($item->nPosTyp, $types, true)
                && $item->isUsedForShippingCostCalculation($iso)
            ) {
                $itemNetValue = (float)$item->fPreisEinzelNetto * (float)$item->nAnzahl;
                $total        += $toGross
                    ? Tax::getGross($itemNetValue, Tax::getSalesTax($item->kSteuerklasse, $iso))
                    : $itemNetValue;
            }
        }

        return $total;
    }

    /**
     * @param CartItem[] $cartItems
     * @return CartItem[]
     */
    private function filterCartItemsByShippingMethod(
        ShippingDTO $shippingMethod,
        array $cartItems,
        string $deliveryCountry,
    ): array {
        $result = [];
        foreach ($cartItems as $cartItem) {
            if ($cartItem->Artikel === null) {
                continue;
            }
            $customCosts = $cartItem->Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN]
                ?? $cartItem->Artikel->FunktionsAttribute[\FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT]
                ?? '';
            if ($shippingMethod->isDependent === false && $customCosts === '') {
                $result[] = $cartItem;
                continue;
            }
            if (
                $shippingMethod->isDependent
                && \is_string($customCosts)
                && \str_contains($customCosts, $deliveryCountry)
            ) {
                $result[] = $cartItem;
            }
        }

        return $result;
    }

    /**
     * @return LanguageModel[]
     */
    private function getAvailableLanguages(): array
    {
        $this->availableLangs ??= $this->getLanguageHelper()->getAvailable();
        return $this->availableLangs;
    }

    private function getDeliveryAddressFromUI(): ?Lieferadresse
    {
        $deliveryAddress = null;
        if (Request::postVar('versandrechnerBTN') === null) {
            return null;
        }
        $deliveryCountry = Request::postVar('land');
        $deliveryZipCode = Request::postVar('plz');
        if (
            \is_string($deliveryCountry)
            && \is_string($deliveryZipCode)
            && $deliveryZipCode !== ''
            && \strlen($deliveryCountry) === 2
        ) {
            $deliveryAddress        = new Lieferadresse();
            $deliveryAddress->cLand = $deliveryCountry;
            $deliveryAddress->cPLZ  = $deliveryZipCode;
        }

        return $deliveryAddress;
    }

    /**
     * @param CartItem[] $cartItems
     */
    private function getCartChecksum(array $cartItems, string $deliveryCountry): string
    {
        $result = [];
        foreach ($cartItems as $item) {
            foreach (self::CART_CHECKSUM_SHIPPING_PROPERTIES as $offset => $property) {
                if (isset($item->$property) === false) {
                    continue;
                }
                $result[$offset][] = $item->$property;
            }
        }
        $result['hasItemsWithCustomCosts'] = $this->cartIsMixed($cartItems, [$deliveryCountry]);

        return \md5(\serialize($result));
    }

    private function getTaxCalculationMethod(): string
    {
        if ($this->taxCalculationMethod !== '') {
            return $this->taxCalculationMethod;
        }
        $this->taxCalculationMethod = Shopsetting::getInstance()->getString(
            Checkout::SHIPPING_TAX_RATE->value,
            \CONF_KAUFABWICKLUNG,
        );

        return $this->taxCalculationMethod;
    }

    /**
     * @param ShippingDTO[] $shippingMethods
     * @return ShippingDTO[]
     * @todo Should we at least check for shipping country? The coupon could not be appliable.
     */
    private function applyShippingFreeCoupon(array $shippingMethods, Currency $currency): array
    {
        if (!empty($this->session->get('VersandKupon'))) {
            $shippingMethods = \array_map(
                static function (ShippingDTO $method) use ($currency): ShippingDTO {
                    return $method->setPrices(
                        (object)[
                            'finalNetCost'   => 0.0,
                            'finalGrossCost' => 0.0,
                            'localizedPrice' => Preise::getLocalizedPriceString(
                                0.0,
                                $currency,
                            ),
                        ]
                    );
                },
                $shippingMethods
            );
        }

        return $shippingMethods;
    }

    /**
     * @param string[]   $countries
     * @param CartItem[] $cartItems
     * @return object{normalShippingItems: int, dependentShippingItems: int}
     */
    private function computeCostTypes(array $cartItems, array $countries = ['']): object
    {
        $normalCostItems = 0;
        $customCostItems = 0;
        foreach ($cartItems as $cartProduct) {
            if ((int)$cartProduct->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL || $cartProduct->Artikel === null) {
                continue;
            }
            foreach ($countries as $country) {
                if ($cartProduct->Artikel->isUsedForShippingCostCalculation($country)) {
                    $normalCostItems++;
                    continue;
                }
                $customCostItems++;
            }
        }

        return (object)[
            'normalShippingItems'    => $normalCostItems,
            'dependentShippingItems' => $customCostItems
        ];
    }

    /**
     * @param ShippingDTO[] $shippingMethods
     * @return ShippingDTO[]
     */
    private function setOptionalDataInShippingMethods(
        array $shippingMethods,
        string $country,
        string $zipCode,
        Customer $customer,
        CustomerGroup $customerGroup,
        Currency $currency,
        array $cartItems = [],
    ): array {
        $result = [];
        foreach ($shippingMethods as $method) {
            $shippingSurcharge = $this->getShippingSurcharge(
                $method,
                $country,
                $zipCode,
                $customerGroup->isMerchant(),
                $currency,
                $cartItems,
            );
            if ($shippingSurcharge !== null) {
                $method = $method->setShippingSurcharge($shippingSurcharge);
            }

            $shippingNetFees = $this->calcCostForShippingMethod(
                $method,
                $country,
                $customer,
                $cartItems,
            );
            if ($shippingNetFees === -1.0) {
                continue;
            }

            $localizedPrice = $customerGroup->isMerchant()
                ? Preise::getLocalizedPriceString(
                    $shippingNetFees,
                    $currency,
                )
                : Preise::getLocalizedPriceString(
                    $this->calculateGrossPrice($shippingNetFees, $cartItems, $country),
                    $currency,
                );

            $result[] = $method->setPrices(
                (object)[
                    'finalNetCost'   => $shippingNetFees,
                    'finalGrossCost' => $this->calculateGrossPrice($shippingNetFees, $cartItems, $country),
                    'localizedPrice' => match ($this->isZero($shippingNetFees)) {
                        true  => $this->getLanguageHelper()->get('freeshipping'),
                        false => $localizedPrice . $this->getVatNote($customerGroup->isMerchant()),
                    },
                ]
            )->setCustomShippingCosts(
                $this->getCustomShippingCostsByCart(
                    $country,
                    $customerGroup,
                    $currency,
                    $cartItems,
                )
            );
        }

        return $result;
    }

    /**
     * @param ShippingDTO[] $shippingMethods
     * @return ShippingDTO[]
     */
    private function filterOutPriciestMethods(array $shippingMethods): array
    {
        if ($shippingMethods === []) {
            return [];
        }
        $result       = [];
        $cheapestCost = \min(
            \array_map(
                static function (ShippingDTO $method): float {
                    return $method->exclFromCheapestCalc
                        ? 99999999.00
                        : $method->finalNetCost;
                },
                $shippingMethods
            )
        );
        foreach ($shippingMethods as $method) {
            if (
                $method->showAlways
                || $method->finalNetCost <= $cheapestCost
            ) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * @return object{qty: float, price: float, country: string}|null
     * @comment The logic comes from legacy code. Could be refactored with a more flexibel approach (regex?).
     */
    private function extractCustomShippingPrices(
        Artikel $product,
        ProductDependentShippingCosts $customCostType,
        string $country,
        float $productQty,
    ): ?object {
        $shippingData = \array_filter(
            \explode(
                ';',
                \str_replace(
                    ',',
                    '.',
                    \trim(
                        $product->FunktionsAttribute[
                        $customCostType === ProductDependentShippingCosts::FIX
                            ? \FKT_ATTRIBUT_VERSANDKOSTEN
                            : \FKT_ATTRIBUT_VERSANDKOSTEN_GESTAFFELT
                        ] ?? ''
                    )
                )
            )
        );

        foreach ($shippingData as $shipping) {
            // $shippingData wenn gestaffelt: DE 1-45,00:2-60,00:3-80;AT 1-90,00:2-120,00:3-150,00
            // $shippingData wenn normal    : DE 600,00;AT 600,00
            $data = \explode(' ', $shipping);
            if ($country !== $data[0] || \count($data) !== 2) {
                continue;
            }
            foreach (\explode(':', $data[1]) as $shippingPrice) {
                if (\str_contains($shippingPrice, '-') === false) {
                    return (object)[
                        'qty'     => $productQty,
                        'price'   => $productQty * (float)$shippingPrice,
                        'country' => $data[0],
                    ];
                }
                $bulkPrice = \explode('-', $shippingPrice);
                $qty       = (float)$bulkPrice[0];
                $price     = (float)$bulkPrice[1];

                if ($qty > 0 && $qty <= $productQty) {
                    $result = (object)[
                        'qty'     => $productQty,
                        'price'   => $price,
                        'country' => $data[0],
                    ];
                }
            }

            return $result ?? null;
        }

        return null;
    }

    private function getCustomShippingCosts(
        string $country,
        Artikel $product,
        float $productQty,
        bool $isMerchant,
        Currency $currency,
        ShippingDTO|null $methodForCustomCostProducts = null,
    ): ?ShippingCartPositionDTO {
        $customCostType = $this->getCustomShippingCostType($product);
        $hookReturn     = false;
        \executeHook(\HOOK_TOOLS_GLOBAL_GIBARTIKELABHAENGIGEVERSANDKOSTEN, [
            'oArtikel'    => &$product,
            'cLand'       => &$country,
            'nAnzahl'     => &$productQty,
            'bHookReturn' => &$hookReturn
        ]);
        if (
            (bool)$hookReturn === true
            || $customCostType === ProductDependentShippingCosts::NONE
        ) {
            return null;
        }

        $customShippingCost = $this->extractCustomShippingPrices(
            $product,
            $customCostType,
            $country,
            $productQty,
        );

        if ($customShippingCost === null) {
            return null;
        }

        $namesLocalized = [];
        foreach ($this->getAvailableLanguages() as $language) {
            $namesLocalized[$language->getCode()] =
                $this->getLanguageHelper()->get('shippingFor', 'checkout')
                . ' '
                . $product->cName
                . ' (' . $customShippingCost->country . ')';
        }
        $netPrice   = ($methodForCustomCostProducts->includeTaxes ?? true)
            ? $this->calculateNetPrice(
                $customShippingCost->price,
                [],
                $country,
                $product->kSteuerklasse,
            )
            : $customShippingCost->price;
        $grossPrice = ($methodForCustomCostProducts->includeTaxes ?? true)
            ? $customShippingCost->price
            : $this->calculateGrossPrice(
                $customShippingCost->price,
                [],
                $country,
                $product->kSteuerklasse,
            );

        return ShippingCartPositionDTO::fromObject(
            (object)[
                'productID'      => $product->kArtikel ?? 0,
                'nameLocalized'  => $namesLocalized,
                'taxClassID'     => $product->kSteuerklasse,
                'netPrice'       => $netPrice,
                'legacyData'     => (object)['fKosten' => $isMerchant ? $netPrice : $grossPrice],
                'priceLocalized' => $isMerchant
                    ? Preise::getLocalizedPriceString(
                        $netPrice,
                        $currency
                    )
                    . ' ' . $this->getLanguageHelper()->get('plus', 'productDetails')
                    . ' ' . $this->getLanguageHelper()->get('vat', 'productDetails')
                    : Preise::getLocalizedPriceString(
                        $grossPrice,
                        $currency
                    ),
            ]
        );
    }

    /**
     * @comment May return gross or net price.
     */
    private function getBulkPrice(ShippingDTO $shippingMethod, float $till): ?float
    {
        if (\count($shippingMethod->bulkPrices) === 0) {
            return $this->database->getBulkPrice(
                $shippingMethod->id,
                $till,
            )->price ?? null;
        }

        $result = null;
        foreach ($shippingMethod->bulkPrices as $bulkPrice) {
            if ($bulkPrice->till >= $till) {
                $result = $bulkPrice->price;
                break;
            }
        }

        return $result;
    }

    /**
     * @param CartItem[] $cartItems
     * @description Returns shipping costs depending on the calculation method or -1.0 if the method cant be used.
     */
    private function getCostsDependingOnCalcMethod(
        ShippingDTO $shippingMethod,
        string $iso,
        array $cartItems
    ): float {
        $result = match ($shippingMethod->calculationType) {
            ShippingCalculationMethod::VM_VERSANDKOSTEN_PAUSCHALE_JTL         => $shippingMethod->price,
            ShippingCalculationMethod::VM_VERSANDBERECHNUNG_GEWICHT_JTL       =>
            $this->getBulkPrice(
                $shippingMethod,
                (float)\array_sum(
                    \array_map(
                        static function (CartItem $cartItem): float {
                            return (float)$cartItem->fGesamtgewicht;
                        },
                        $cartItems
                    )
                )
            ),
            ShippingCalculationMethod::VM_VERSANDBERECHNUNG_WARENWERT_JTL     =>
            $this->getBulkPrice(
                $shippingMethod,
                (float)\array_sum(
                    \array_map(
                        static function (CartItem $cartItem) use ($shippingMethod): float {
                            $totalNetPrice = (float)($cartItem->fPreisEinzelNetto ?? 0.0)
                                * (float)($cartItem->nAnzahl ?? 0.0);

                            return $shippingMethod->includeTaxes
                                ? $totalNetPrice * ((CartItem::getTaxRate($cartItem) / 100) + 1)
                                : $totalNetPrice;
                        },
                        $cartItems
                    )
                )
            ),
            ShippingCalculationMethod::VM_VERSANDBERECHNUNG_ARTIKELANZAHL_JTL =>
            $this->getBulkPrice(
                $shippingMethod,
                (float)\array_sum(
                    \array_map(
                        static function (CartItem $cartItem): int {
                            return $cartItem->istKonfig() === false
                                ? (int)$cartItem->nAnzahl
                                : 0;
                        },
                        $cartItems
                    )
                )
            ),
        };

        if ($result === null) {
            return -1.0;
        }

        return $shippingMethod->includeTaxes
            ? $this->calculateNetPrice(
                $result,
                $cartItems,
                $iso,
                $this->getTaxRateIDs(
                    $this->getTaxCalculationMethod(),
                    $cartItems,
                    $iso,
                )[0]->taxRateID ?? null
            )
            : $result;
    }

    private function validatePaymentModuleIntern(
        int $minRequiredOrderCount,
        float $minRequiredCartValue,
        float $maxAllowedCartValue,
        int $customerOrderCount,
        float $currentCartGrossValue
    ): bool {
        if ($minRequiredOrderCount > 0 && $customerOrderCount < $minRequiredOrderCount) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMinBestellungen Bestellanzahl zu niedrig: Anzahl {cnt} < {min}',
                ['cnt' => $customerOrderCount, 'min' => $minRequiredOrderCount]
            );

            return false;
        }

        if ($minRequiredCartValue > 0 && $currentCartGrossValue < $minRequiredCartValue) {
            Shop::Container()->getLogService()->debug(
                'checkMinOrderValue Bestellwert zu niedrig: Wert {crnt} < {min}',
                ['crnt' => $currentCartGrossValue, 'min' => $minRequiredCartValue]
            );

            return false;
        }

        if ($maxAllowedCartValue > 0 && $currentCartGrossValue > $maxAllowedCartValue) {
            Shop::Container()->getLogService()->debug(
                'pruefeZahlungsartMaxBestellwert Bestellwert zu hoch: Wert {crnt} > {max}',
                ['crnt' => $currentCartGrossValue, 'max' => $maxAllowedCartValue]
            );

            return false;
        }

        return true;
    }

    private function validatePaymentModuleExtern(
        PaymentDTO $paymentMethod,
        int $customerOrderCount,
        float $cartGrossValue,
    ): bool {
        try {
            $conf = Shop::getSettingSection(\CONF_PLUGINZAHLUNGSARTEN);
        } catch (\InvalidArgumentException) {
            $conf = [];
        }
        /** @var array<string, int|float> $conf */
        if (
            $this->validatePaymentModuleIntern(
                (int)($conf[$paymentMethod->modulID . '_min_bestellungen'] ?? 0),
                (float)($conf[$paymentMethod->modulID . '_min'] ?? 0),
                (float)($conf[$paymentMethod->modulID . '_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            )
        ) {
            $payMethod = LegacyMethod::create($paymentMethod->modulID);
            return $payMethod === null
                || $payMethod->isValidIntern(
                    [
                        Frontend::getCustomer(),
                        Frontend::getCart()
                    ]
                );
        }

        return false;
    }

    private function validatePaymentMethod(
        PaymentDTO $paymentMethod,
        int $customerOrderCount = 0,
        float $cartGrossValue = 0
    ): bool {
        if (!isset($paymentMethod->cModulId)) {
            return false;
        }
        $conf = Shop::getSettingSection(\CONF_ZAHLUNGSARTEN);
        /** @var array<string, int|float> $conf */

        return match ($paymentMethod->cModulId) {
            'za_ueberweisung_jtl' => $this->validatePaymentModuleIntern(
                (int)($conf['zahlungsart_ueberweisung_min_bestellungen'] ?? 0),
                (float)($conf['zahlungsart_ueberweisung_min'] ?? 0),
                (float)($conf['zahlungsart_ueberweisung_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            ),
            'za_nachnahme_jtl'    => $this->validatePaymentModuleIntern(
                (int)($conf['zahlungsart_nachnahme_min_bestellungen'] ?? 0),
                (float)($conf['zahlungsart_nachnahme_min'] ?? 0),
                (float)($conf['zahlungsart_nachnahme_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            ),
            'za_rechnung_jtl'     => $this->validatePaymentModuleIntern(
                (int)($conf['zahlungsart_rechnung_min_bestellungen'] ?? 0),
                (float)($conf['zahlungsart_rechnung_min'] ?? 0),
                (float)($conf['zahlungsart_rechnung_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            ),
            'za_lastschrift_jtl'  => $this->validatePaymentModuleIntern(
                (int)($conf['zahlungsart_lastschrift_min_bestellungen'] ?? 0),
                (float)($conf['zahlungsart_lastschrift_min'] ?? 0),
                (float)($conf['zahlungsart_lastschrift_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            ),
            'za_barzahlung_jtl'   => $this->validatePaymentModuleIntern(
                (int)($conf['zahlungsart_barzahlung_min_bestellungen'] ?? 0),
                (float)($conf['zahlungsart_barzahlung_min'] ?? 0),
                (float)($conf['zahlungsart_barzahlung_max'] ?? 0),
                $customerOrderCount,
                $cartGrossValue,
            ),
            'za_null_jtl'         => true,
            default               => $this->validatePaymentModuleExtern(
                $paymentMethod,
                $customerOrderCount,
                $cartGrossValue,
            )
        };
    }

    /**
     * @param ShippingDTO[] $possibleMethods
     * @param string[]      $allowedCountries
     * @param CartItem[]    $cartItems
     * @return ShippingDTO[]
     */
    private function filterByCountriesAndShippingClasses(
        array $possibleMethods,
        array $allowedCountries,
        array $cartItems,
        int $customerGroupID,
    ): array {
        $result             = [];
        $shippingClasses    = $this->getShippingClasses($cartItems);
        $cartGrossValue     = $this->getItemsTotalValue(
            [\C_WARENKORBPOS_TYP_ARTIKEL],
            $cartItems,
            true,
        );
        $customerOrderCount = $this->session->getOrderCount();
        $this->session->set('orderCount', $customerOrderCount);

        foreach ($possibleMethods as $shippingMethod) {
            $allowedCountriesForMethod = empty($allowedCountries)
                ? $shippingMethod->countries
                : \array_intersect(
                    $shippingMethod->countries,
                    $allowedCountries,
                );
            if ($shippingMethod->isDependent !== $this->cartIsDependent($cartItems, $allowedCountriesForMethod)) {
                continue;
            }

            $hasValidPaymentMethod = false;
            foreach ($this->getPossiblePaymentMethods($shippingMethod->id, $customerGroupID) as $paymentMethod) {
                $paymentValidationOffset = \md5($paymentMethod->id . $customerOrderCount . $cartGrossValue);

                $this->paymentMethodValidation[$paymentValidationOffset] ??= $this->validatePaymentMethod(
                    $paymentMethod,
                    $customerOrderCount,
                    $cartGrossValue
                );
                if ($this->paymentMethodValidation[$paymentValidationOffset] ?? false) {
                    $hasValidPaymentMethod = true;
                    break;
                }
            }
            if ($hasValidPaymentMethod === false) {
                continue;
            }

            if (\in_array('-1', $shippingMethod->allowedShippingClasses, true)) {
                $result[] = $shippingMethod;
                continue;
            }
            foreach ($shippingMethod->allowedShippingClasses as $allowedClassesCombination) {
                $allowedShippingClasses = \explode('-', $allowedClassesCombination);
                $shippingClassCount     = \count($shippingClasses);
                if (\count($allowedShippingClasses) !== $shippingClassCount) {
                    continue;
                }
                if (
                    \count(
                        \array_intersect(
                            \array_map(
                                '\intval',
                                $allowedShippingClasses
                            ),
                            $shippingClasses
                        )
                    ) === $shippingClassCount
                ) {
                    $result[] = $shippingMethod;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @return ShippingDTO[]
     */
    private function getMethodsFromCacheOrDB(
        string $deliveryCountryCode,
        int $customerGroupID,
    ): array {
        $possibleMethods = $this->cache->getPossibleMethods(
            $deliveryCountryCode,
            $customerGroupID,
        );
        if ($possibleMethods === []) {
            $shippingMethodsOffset = \md5($customerGroupID . $deliveryCountryCode);
            $possibleMethods       = $this->possibleShippingMethods[$shippingMethodsOffset]
                ?? $this->database->getShippingMethods(
                    $customerGroupID,
                    $this->getAvailableLanguages(),
                    $deliveryCountryCode,
                );
            if ($possibleMethods === []) {
                return [];
            }
            $this->possibleShippingMethods[$shippingMethodsOffset] = $possibleMethods;
            $this->cache->setPossibleMethods(
                $possibleMethods,
                $deliveryCountryCode,
                $customerGroupID,
            );
        }

        return $possibleMethods;
    }

    /**
     * @todo Review this method and take care of config items, variations and so on.
     */
    private function cartItemFromProduct(Artikel $product, int $customerGroupID = 0, int $customerID = 0): CartItem
    {
        if ($product->Preise === null) {
            $product->holPreise(
                $customerGroupID,
                $product,
                $customerID,
            );
        }
        $unique                      = '';
        $cartItem                    = new CartItem();
        $cartItem->Artikel           = $product;
        $cartItem->nAnzahl           = 1;
        $cartItem->kArtikel          = $product->kArtikel;
        $cartItem->kVersandklasse    = $product->kVersandklasse ?? 0;
        $cartItem->kSteuerklasse     = $product->kSteuerklasse ?? 0;
        $cartItem->fPreisEinzelNetto = $product->Preise->fVKNetto ?? 0.00;
        $cartItem->fPreis            = $cartItem->fPreisEinzelNetto;
        $cartItem->cArtNr            = $product->cArtNr ?? '';
        $cartItem->nPosTyp           = \C_WARENKORBPOS_TYP_ARTIKEL;
        $cartItem->cEinheit          = $product->cEinheit ?? '';
        $cartItem->cUnique           = $unique;
        $cartItem->cResponsibility   = 'core';
        $cartItem->kKonfigitem       = 0;
        $cartItem->cName             = [];
        $cartItem->cLieferstatus     = [];
        $cartItem->fVK               = $product->Preise->fVK ?? [];
        $cartItem->fGesamtgewicht    = $cartItem->gibGesamtgewicht();

        if ($product->isKonfigItem) {
            $cartItem->cUnique     = \uniqid('', true);
            $cartItem->kKonfigitem = $product->kArtikel;
        }

        return $cartItem;
    }

    /**
     * @param CartItem[] $cartItems
     */
    private function getPredominantTaxRate(array $cartItems): int
    {
        $taxRates = [];
        foreach ($cartItems as $item) {
            if (
                $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $item->kSteuerklasse > 0
            ) {
                $taxRates[$item->kSteuerklasse] = ($taxRates[$item->kSteuerklasse] ?? 0.00)
                    + ((float)$item->fPreisEinzelNetto * (int)$item->nAnzahl);
            }
        }

        return empty($taxRates) === false
            ? (int)\array_search(
                \max($taxRates),
                $taxRates,
                true
            )
            : 0;
    }

    /**
     * @param CartItem[] $cartItems
     */
    private function getHighestTaxRate(array $cartItems, string $country = ''): int
    {
        $rate      = -1;
        $taxRateID = 0;
        foreach ($cartItems as $item) {
            if (
                $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $item->kSteuerklasse > 0
                && $this->getTaxRate($country, $item->kSteuerklasse) > $rate
            ) {
                $rate      = $this->getTaxRate($country, $item->kSteuerklasse);
                $taxRateID = $item->kSteuerklasse;
            }
        }

        return $taxRateID;
    }

    /**
     * @param CartItem[] $cartItems
     * @return array<int, object{taxRateID: int, proportion: float}>
     */
    private function getProportionalTaxRates(array $cartItems): array
    {
        $result    = [];
        $taxRates  = [];
        $cartValue = 0.00;
        foreach ($cartItems as $item) {
            if (
                $item->nPosTyp === \C_WARENKORBPOS_TYP_ARTIKEL
                && $item->kSteuerklasse > 0
            ) {
                $itemValue                      = (float)$item->fPreisEinzelNetto * (int)$item->nAnzahl;
                $cartValue                      += $itemValue;
                $taxRates[$item->kSteuerklasse] = ($taxRates[$item->kSteuerklasse] ?? 0.00)
                    + $itemValue;
            }
        }
        foreach ($taxRates as $taxRateID => $itemValueInCart) {
            $result[] = (object)[
                'taxRateID'  => $taxRateID,
                'proportion' => \round(
                    ($itemValueInCart * 100) / $cartValue,
                    2
                ),
            ];
        }

        return $result;
    }

    /**
     * @param ShippingDTO[] $possibleMethods
     */
    public function getFavourableShippingMethod(array $possibleMethods): ShippingDTO|null
    {
        $possibleMethods = \array_filter(
            $possibleMethods,
            static fn($method) => $method->exclFromCheapestCalc === false
        );
        if ($possibleMethods === []) {
            return null;
        }

        return $this->sortMethodsByPrice($possibleMethods)[0] ?? null;
    }
}
