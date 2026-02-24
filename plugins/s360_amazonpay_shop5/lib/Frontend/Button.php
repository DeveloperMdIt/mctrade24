<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Frontend;

use JTL\Checkout\Bestellung;
use JTL\Checkout\Lieferadresse;
use JTL\Language\LanguageHelper;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\PaymentDetails;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SessionController;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException;
use Plugin\s360_amazonpay_shop5\lib\Mappers\AddressMapper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlCartHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;

/**
 * Class Button
 *
 * This class represents a frontend Amazon Pay-Button.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Frontend
 */
class Button {

    public const TYPE_LOGIN = 'login';
    public const TYPE_PAY = 'pay';

    public const DISPLAY_TYPE_LOGIN = 'LwA';
    public const DISPLAY_TYPE_PAY = 'PwA';

    public const CONTEXT_LOGIN = 'login';
    public const CONTEXT_PAY_GLOBAL = 'pay';
    public const CONTEXT_PAY_DETAIL = 'payDetail';
    public const CONTEXT_PAY_CATEGORY = 'payCategory';
    public const CONTEXT_APB_REDIRECT = 'apbRedirect';

    private const DEFAULT_LANGUAGE = 'de-DE';

    private const LANGUAGE_MAPPING = [
        'ger' => 'de-DE',
        'eng' => 'en-GB',
        'fre' => 'fr-FR',
        'ita' => 'it-IT',
        'spa' => 'es-ES'
    ];

    // APIV2: Supported languages depend on the region, and the format differs from API v1
    private const DEFAULT_LANGUAGES = [
        Config::REGION_DE => 'de_DE',
        Config::REGION_EU => 'de_DE',
        Config::REGION_UK => 'en_GB',
        Config::REGION_US => 'en_US',
        Config::REGION_NA => 'en_US',
        Config::REGION_JP => 'jp_JP'
    ];

    // APIV2: Supported languages depend on the region, and the format differs from API v1
    private const REGION_LANGUAGE_MAPPING = [
        Config::REGION_DE => [
            'ger' => 'de_DE',
            'eng' => 'en_GB',
            'fre' => 'fr_FR',
            'ita' => 'it_IT',
            'spa' => 'es_ES'
        ],
        Config::REGION_EU => [
            'ger' => 'de_DE',
            'eng' => 'en_GB',
            'fre' => 'fr_FR',
            'ita' => 'it_IT',
            'spa' => 'es_ES'
        ],
        Config::REGION_UK => [
            'ger' => 'de_DE',
            'eng' => 'en_GB',
            'fre' => 'fr_FR',
            'ita' => 'it_IT',
            'spa' => 'es_ES'
        ],
        Config::REGION_US => [
            'ger' => 'en_US',
            'eng' => 'en_US',
            'fre' => 'en_US',
            'ita' => 'en_US',
            'spa' => 'en_US'
        ],
        Config::REGION_NA => [
            'ger' => 'en_US',
            'eng' => 'en_US',
            'fre' => 'en_US',
            'ita' => 'en_US',
            'spa' => 'en_US'
        ],
        Config::REGION_JP => [
            'ger' => 'jp_JP',
            'eng' => 'jp_JP',
            'fre' => 'jp_JP',
            'ita' => 'jp_JP',
            'spa' => 'jp_JP'
        ]
    ];

    private const DEFAULT_LEDGER_CURRENCY = 'EUR';
    private const DEFAULT_PRODUCT_TYPE = 'PayAndShip';
    private const LOGIN_PRODUCT_TYPE = 'SignIn';

    public const PLACEMENT_HOME = 'Home';
    public const PLACEMENT_PRODUCT = 'Product';
    public const PLACEMENT_CART = 'Cart';
    public const PLACEMENT_CHECKOUT = 'Checkout';
    public const PLACEMENT_OTHER = 'Other';


    /**
     * The type of the button.
     * @var string $type
     */
    private $type;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * The context where this button is displayed
     * @var string $context
     */
    private $context;

    /**
     * Our plugin.
     * @var PluginInterface $plugin
     */
    private $plugin;

    /**
     * The color according to Amazon Pay definitions.
     * @var string $color
     */
    private $color;

    /**
     * The height of the button in px.
     * Soft limits are 45-190
     */
    private $height;

    /**
     * The display type of this button (i.e. full-text or just a logo)
     * @var string $displayType
     */
    private $displayType;

    /**
     * Whether we are in sandbox or not.
     * @var bool $sandbox
     */
    private $sandbox;

    /**
     * Internal ID/used in rendered buttons.
     */
    private $id;

    /**
     * The payload for this button (this is a stringified JSON object!)
     */
    private $payload;

    /**
     * The signature for this button, note that this highly depends on the payload itself! order of elements matters!
     */
    private $signature;

    /**
     * Subscription intervals.
     * Array of intervals that should be available as options for this button.
     */
    private $subscriptionIntervals;

    /**
     * The currently selected subscription interval.
     * @var Interval
     */
    private $selectedSubscriptionInterval;

    /**
     * CSS column classes for the container of the rendered button.
     * @var string $cssColumns
     */
    private $cssColumns;

    /**
     * A value used to display the possible savings with subscription, if it is given and > 0
     * @var string $subscriptionDiscountRate
     */
    private $subscriptionDiscountRate;

    /**
     * Button constructor.
     * @param string $type
     * @param string $context
     * @param array $subscriptionIntervals
     * @param float|int $subscriptionDiscountRate
     * @throws TechnicalException
     */
    public function __construct(string $type, string $context, array $subscriptionIntervals = [], string $subscriptionDiscountRate = '') {
        $this->type = $type;
        $this->plugin = Plugin::getInstance();
        $this->context = $context;
        $this->config = Config::getInstance();
        $this->sandbox = $this->config->isSandbox();
        if (!empty($subscriptionIntervals)) {
            usort($subscriptionIntervals, static function ($a, $b) {
                /** @var Interval $a */
                /** @var Interval $b */
                return $a->compareTo($b);
            });
        }
        $this->subscriptionIntervals = $subscriptionIntervals;
        $this->subscriptionDiscountRate = $subscriptionDiscountRate;
        $this->selectedSubscriptionInterval = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
        switch ($this->context) {
            case self::CONTEXT_LOGIN:
                $this->color = $this->config->getButtonLoginColor();
                $this->cssColumns = $this->config->getButtonLoginCssColumns();
                $this->height = $this->config->getButtonLoginHeight();
                $this->displayType = self::DISPLAY_TYPE_LOGIN;
                $this->payload = $this->createLoginButtonPayload();
                $this->signature = $this->createButtonSignature($this->payload);
                break;
            case self::CONTEXT_PAY_GLOBAL:
            case self::CONTEXT_APB_REDIRECT:
                $this->color = $this->config->getButtonPayColor();
                $this->cssColumns = $this->config->getButtonPayCssColumns();
                $this->height = $this->config->getButtonPayHeight();
                $this->displayType = self::DISPLAY_TYPE_PAY;
                $this->payload = '';
                $this->signature = '';
                break;
            case self::CONTEXT_PAY_DETAIL:
                $this->color = $this->config->getButtonPayDetailColor();
                $this->cssColumns = $this->config->getButtonPayDetailCssColumns();
                $this->height = $this->config->getButtonPayDetailHeight();
                $this->displayType = self::DISPLAY_TYPE_PAY;
                $this->payload = '';
                $this->signature = '';
                break;
            case self::CONTEXT_PAY_CATEGORY:
                $this->color = $this->config->getButtonPayCategoryColor();
                $this->cssColumns = $this->config->getButtonPayCategoryCssColumns();
                $this->height = $this->config->getButtonPayCategoryHeight();
                $this->displayType = self::DISPLAY_TYPE_PAY;
                $this->payload = '';
                $this->signature = '';
                break;
            default:
                throw new TechnicalException('Unrecognized button context: "' . $this->context . '"');
        }
        $this->id = mb_ereg_replace('\.', '', uniqid('lpa-button-' . $this->context . '-', true));
    }

    /**
     * Creates a smarty-usable config for the button.
     *
     * See button.tpl for usage of the values.
     *
     * @param array $options
     * @return array
     */
    public function createSmartyConfig($options = []): array {
        $linkHelper = JtlLinkHelper::getInstance();
        ['amount' => $estimatedOrderAmountAmount, 'currency' => $estimatedOrderAmountCurrency] = JtlCartHelper::getInstance()->getEstimatedOrderAmount();
        $result = [
            'classes' => 'lpa-button lpa-button-' . $this->getType() . ' lpa-button-context-' . $this->getContext(),
            'id' => $this->id,
            'sellerId' => $this->getConfig()->getMerchantId(),
            'scope' => Constants::DEFAULT_SCOPE,
            'toolTipText' => Translation::getInstance()->get(Translation::KEY_BUTTON_TOOLTIP),
            'type' => $this->getDisplayType(),
            'color' => $this->color,
            'height' => $this->getHeight(),
            'width' => '100%', // for compatibility reasons we still deliver this into templates
            'cssColumns' => $this->getCssColumns(),
            'redirectUrl' => $linkHelper->getFullReturnUrl(),
            'context' => $this->getContext(),
            'requiredFieldMissingMessage' => LanguageHelper::getInstance()->getTranslation('mandatoryFieldNotification', 'errorMessages'),
            'frontendTemplatePath' => $this->plugin->getPaths()->getFrontendPath() . 'template/',
            'sandbox' => $this->sandbox,
            'alignment' => 'default', // for compatibility reasons we still deliver this into templates
            'payload' => $this->payload,
            'signature' => $this->signature,
            'publicKeyId' => $this->config->getPublicKeyId(),
            'subscriptionIntervals' => $this->subscriptionIntervals,
            'selectedSubscriptionInterval' => $this->selectedSubscriptionInterval,
            'subscriptionLabelTitle' => Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_INTERVAL_SELECT_LABEL_TITLE),
            'subscriptionLabelText' => Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_INTERVAL_SELECT_LABEL_TEXT),
            'subscriptionNoneText' => Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_INTERVAL_DISPLAY_NONE),
            'subscriptionDiscountText' => empty($this->subscriptionDiscountRate) ? '' : str_replace('#amount#', $this->subscriptionDiscountRate, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_DISCOUNT_RATE_HINT)),
            'estimatedOrderAmountAmount' => empty($estimatedOrderAmountAmount) ? null : number_format($estimatedOrderAmountAmount, 2, '.', ''),
            'estimatedOrderAmountCurrency' => empty($estimatedOrderAmountCurrency) ? null : $estimatedOrderAmountCurrency,
            'ioPath' => Compatibility::isShopAtLeast54() ? '/io' : '/io.php'
        ];

        $placement = self::PLACEMENT_OTHER;
        switch (Shop::getPageType()) {
            case PAGE_ARTIKEL:
            case PAGE_ARTIKELLISTE:
                $placement = self::PLACEMENT_PRODUCT;
                break;
            case PAGE_BESTELLVORGANG:
                $placement = self::PLACEMENT_CHECKOUT;
                break;
            case PAGE_WARENKORB:
                $placement = self::PLACEMENT_CART;
                break;
            case PAGE_STARTSEITE:
                $placement = self::PLACEMENT_HOME;
                break;
            default:
                break;
        }

        if ($this->context === self::CONTEXT_APB_REDIRECT) {
            $placement = self::PLACEMENT_OTHER; // Use 'OTHER' as agreed upon with Amazon Pay
        }

        $result['placement'] = $placement;
        if ($this->context === self::CONTEXT_LOGIN) {
            $result['productType'] = self::LOGIN_PRODUCT_TYPE;
        } else {
            $result['productType'] = self::DEFAULT_PRODUCT_TYPE;
        }

        $region = $this->config->getRegion();
        $languageCode = Shop::getLanguageCode();
        $language = self::DEFAULT_LANGUAGES[$region];
        $languageMapping = self::REGION_LANGUAGE_MAPPING[$region];
        if (null !== $languageCode && array_key_exists(mb_strtolower($languageCode), $languageMapping)) {
            $language = $languageMapping[mb_strtolower($languageCode)];
        }
        $result['language'] = $language;

        // Load this by region, US supports USD only, in Europe it can be EUR or GBP, etc.
        $ledgerCurrency = self::DEFAULT_LEDGER_CURRENCY;
        switch ($region) {
            case Config::REGION_DE:
            case Config::REGION_EU:
                $ledgerCurrency = 'EUR';
                break;
            case Config::REGION_UK:
                $ledgerCurrency = 'GBP';
                break;
            case Config::REGION_NA:
            case Config::REGION_US:
                $ledgerCurrency = 'USD';
                break;
            case Config::REGION_JP:
                $ledgerCurrency = 'JPY';
                break;
            default:
                break;
        }
        $result['ledgerCurrency'] = $ledgerCurrency;

        if (!empty($options)) {
            $result = array_merge($result, $options);
        }

        return $result;
    }

    /**
     * Returns the rendered HTML for this button.
     * @param array $options
     * @return string
     * @throws \SmartyException
     */
    public function render($options = []) {
        Shop::Smarty()->assign('lpaButton', $this->createSmartyConfig($options));
        if (file_exists(__DIR__ . '/../../frontend/template/snippets/button_custom.tpl')) {
            return Shop::Smarty()->fetch(__DIR__ . '/../../frontend/template/snippets/button_custom.tpl');
        }
        return Shop::Smarty()->fetch(__DIR__ . '/../../frontend/template/snippets/button.tpl');
    }

    /**
     * @return Config
     */
    private function getConfig(): Config {
        return $this->config;
    }

    /**
     * @return string
     */
    private function getType(): string {
        return $this->type;
    }

    /**
     * @return string
     */
    private function getContext(): string {
        return $this->context;
    }

    /**
     * @return string
     */
    private function getDisplayType(): string {
        return $this->displayType;
    }

    private function getCssColumns() {
        return $this->cssColumns;
    }

    /**
     * @return mixed
     */
    private function getHeight() {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @throws \Exception
     */
    public function setAdditionalPaymentButtonPayload(Bestellung $order) {

        // success so far, let's update the checkout session such that Amazon Pay knows the appropriate amount
        $currency = new \JTL\Catalog\Currency((int)$order->kWaehrung);
        // first, check if the user currency is valid
        if (!Currency::getInstance()->isSupportedCurrency($currency->getCode())) {
            // this is an error
            throw new \Exception('CurrencyMismatch');
        }

        $merchantMetadata = new MerchantMetadata();
        $merchantMetadata->setCustomInformation($this->config->getCustomInformation());
        $storeName = Shop::getSettings([CONF_GLOBAL])['global']['global_shopname'];
        if (!empty($storeName)) {
            $storeName = html_entity_decode($storeName, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
            $merchantMetadata->setMerchantStoreName(mb_substr($storeName, 0, 50));
        }

        $jtlDeliveryAddress = null;
        if (isset($order->Lieferadresse)) {
            // new delivery address
            $jtlDeliveryAddress = $order->Lieferadresse;
        } elseif (isset($order->kLieferadresse) && (int)$order->kLieferadresse > 0) {
            // existing delivery address
            $jtlDeliveryAddress = new Lieferadresse((int)$order->kLieferadresse);
        } else {
            // assume billing address is delivery address
            $jtlDeliveryAddress = $order->oRechnungsadresse;
        }

        if ($jtlDeliveryAddress === null) {
            throw new \Exception('MissingShippingAddress');
        }

        $addressDetails = AddressMapper::mapAddressJtlToAmazon($jtlDeliveryAddress);
        $addressDetailsArray = [
            'name' => $addressDetails->getName(),
            'addressLine1' => $addressDetails->getAddressLine1(),
            'addressLine2' => $addressDetails->getAddressLine2(),
            'city' => $addressDetails->getCity(),
            'postalCode' => $addressDetails->getPostalCode(),
            'districtOrCounty' => $addressDetails->getDistrict(),
            'stateOrRegion' => $addressDetails->getStateOrRegion(),
            'countryCode' => $addressDetails->getCountryCode(),
            'phoneNumber' => $addressDetails->getPhoneNumber() === '' ? '0' : $addressDetails->getPhoneNumber() // Note: we are officially allowed to default to '0' if no phone number is present on the address
        ];

        $payloadArray = [
            'webCheckoutDetails' => [
                'checkoutResultReturnUrl' => JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CALLBACK_RESULT),
                'checkoutMode' => 'ProcessOrder'
            ],
            'storeId' => $this->config->getClientId(),
            'paymentDetails' => [
                'paymentIntent' => PaymentDetails::PAYMENT_INTENT_AUTHORIZE, // TODOLATER: For now we only ever use Authorize
                'chargeAmount' => [
                    'amount' => Currency::convertToAmazonString($order->fGesamtsumme * $order->fWaehrungsFaktor),
                    'currencyCode' => $currency->getCode()
                ],
                'canHandlePendingAuthorization' => $this->config->getAuthorizationMode() === Config::AUTHORIZATION_MODE_OMNI
            ],
            'merchantMetadata' => [
                'customInformation' => $merchantMetadata->getCustomInformation(),
                'merchantStoreName' => $merchantMetadata->getMerchantStoreName()
            ],
            'platformId' => Config::getInstance()->getPlatformId(),
            'addressDetails' => $addressDetailsArray
        ];
        if (!Currency::getInstance()->isLedgerCurrency($currency->getCode())) {
            $payloadArray['paymentDetails']['presentmentCurrency'] = $currency->getCode();
        }

        $this->payload = json_encode($payloadArray, JSON_UNESCAPED_UNICODE);
        $this->signature = $this->createButtonSignature($this->payload);
    }

    private function createLoginButtonPayload() {
        $payloadArray = [
            'signInReturnUrl' => JtlLinkHelper::getInstance()->getFullReturnUrl(),
            'signInScopes' => ['name', 'email'],
            'storeId' => $this->config->getClientId()
        ];
        return json_encode($payloadArray, JSON_UNESCAPED_UNICODE);
    }

    private function createButtonSignature($payload) {
        return (new ApiAdapter())->signPayload($payload);
    }

}
