<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

/**
 * Class Translation
 *
 * Handles code-side translations of plugin language variables.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Translation {

    public const KEY_ACCOUNT_MERGE_REQUIRED = 'account_merge_required';
    public const KEY_ACCOUNT_MERGE_SUCCESSFUL = 'account_merge_successful';
    public const KEY_CHECKOUT_CART_CHANGED_REDIRECT = 'checkout_cart_changed_redirect';
    public const KEY_CHECKOUT_CURRENCY_CHANGED = 'checkout_currency_changed';
    public const KEY_CHECKOUT_ADDRESS_CHANGED = 'checkout_address_changed';
    public const KEY_CREATE_DESCRIPTION = 'create_description';
    public const KEY_CREATE_DESCRIPTION_OPTIONAL = 'create_description_optional';
    public const KEY_CREATE_DESCRIPTION_GUEST = 'create_description_guest';
    public const KEY_CREATE_DESCRIPTION_BILLING_OVERRIDE = 'create_description_billing_override';
    public const KEY_CREATE_DESCRIPTION_OPTIONAL_BILLING_OVERRIDE = 'create_description_optional_billing_override';
    public const KEY_CREATE_DESCRIPTION_GUEST_BILLING_OVERRIDE = 'create_description_guest_billing_override';
    public const KEY_ERROR_GENERIC = 'error_generic';
    public const KEY_ERROR_REDIRECT = 'error_redirect';
    public const KEY_ERROR_MISSING_ORID = 'error_missing_orid';
    public const KEY_ERROR_NOT_LOGGED_IN = 'error_not_logged_in';
    public const KEY_HARD_DECLINE = 'hard_decline';
    public const KEY_LOGIN_NOT_ALLOWED = 'login_not_allowed';
    public const KEY_RETURN_ERROR_GENERIC = 'return_error_generic';
    public const KEY_RETURN_ERROR_NO_CHECKOUT = 'return_error_no_checkout';
    public const KEY_SOFT_DECLINE = 'soft_decline';
    public const KEY_FAIL_SAFE_CONFIRMATION = 'fail_safe_confirmation';
    public const KEY_ASYNC_AUTH_HINT = 'async_auth_hint';
    public const KEY_BUTTON_TOOLTIP = 'button_tooltip';
    public const KEY_BEHAVIORAL_OVERLAY_TITLE = 'behavioral_overlay_title';
    public const KEY_BEHAVIORAL_OVERLAY_TEXT = 'behavioral_overlay_text';
    public const KEY_NO_SHIPPING_METHOD = 'no_shipping_method';
    public const KEY_NO_SHIPPING_ADDRESS = 'no_shipping_address';
    public const KEY_SELECT_SHIPPING_METHOD = 'select_shipping_method';
    public const KEY_PACKSTATION_NOT_ALLOWED = 'packstation_not_allowed';
    public const KEY_PAYMENT_NOT_SUCCESSFUL = 'payment_not_successful';
    public const KEY_MERCHANT_INFO_FAILED_RECHARGE_ON_EXPIRED = 'merchant_info_failed_recharge_on_expired';
    public const KEY_MERCHANT_INFO_AMAZON_CANCELED_CHARGE = 'merchant_info_amazon_canceled_charge';
    public const KEY_CLOSURE_REASON_STORNO = 'closure_reason_storno';
    public const KEY_MERCHANT_INFO_FAILED_CAPTURE = 'merchant_info_failed_capture';
    public const KEY_SUBSCRIPTION_CUSTOMER_CANCELED = 'subscription_customer_canceled';
    public const KEY_SUBSCRIPTION_CUSTOMER_CANCEL_FAILED = 'subscription_customer_cancel_failed';
    public const KEY_SUBSCRIPTION_INTERVAL_SELECT_LABEL_TITLE = 'subscription_interval_select_label_title';
    public const KEY_SUBSCRIPTION_INTERVAL_SELECT_LABEL_TEXT = 'subscription_interval_select_label_text';
    public const KEY_SUBSCRIPTION_INTERVAL_DISPLAY_NONE = 'subscription_interval_display_none';
    public const KEY_SUBSCRIPTION_ERROR_REDIRECT = 'subscription_error_redirect';
    public const KEY_SUBSCRIPTION_CHECKOUT_HINT = 'subscription_checkout_hint';
    public const KEY_SUBSCRIPTION_CREATED_HINT = 'subscription_created_hint';
    public const KEY_SUBSCRIPTION_FAILED_HINT = 'subscription_failed_hint';
    public const KEY_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE = 'subscription_customer_subscriptions_available';
    public const KEY_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE_LINK = 'subscription_customer_subscriptions_available_link';
    public const KEY_SUBSCRIPTION_DISCOUNT_RATE_HINT = 'subscription_discount_rate_hint';
    public const KEY_SUBSCRIPTION_DISCOUNT_CART_POSITION = 'subscription_discount_cart_position';


    private $plugin;

    private static $instance;

    private function __construct() {
        $this->plugin = Plugin::getInstance();
    }

    public static function getInstance(): Translation {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function get(string $key, $languageIso = null): ?string {
        if(empty($languageIso)) {
            $result = $this->plugin->getLocalization()->getTranslation($key);
            return $result ?? $key;
        }
        return $this->plugin->getLocalization()->getTranslations()[$key][\mb_convert_case($languageIso, \MB_CASE_UPPER)];
    }
}