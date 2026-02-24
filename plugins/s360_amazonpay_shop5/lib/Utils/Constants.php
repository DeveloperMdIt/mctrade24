<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

/**
 * Class Constants
 *
 * Provides constants that are used around the plugin.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Constants {
    public const PAYMENT_METHOD_NAME = 'Amazon Pay';
    public const PLUGIN_ID = 's360_amazonpay_shop5';
    public const COOKIE_ACCESS_TOKEN = 'amazon_Login_accessToken';
    public const DEFAULT_SCOPE = 'profile payments:widget payments:shipping_address payments:billing_address';
    public const MAX_TRANSACTION_TIMEOUT = 1440;
    public const ORDER_ATTRIBUTE_REFERENCE_ID = 'AmazonPay-Referenz';
    public const DEVELOPMENT_MODE_CONSTANT = 'AMAZONPAY_DEVELOPMENT_MODE';
    public const DESYNC_ORDER_CHECK_CONSTANT = 'AMAZONPAY_CHECK_DESYNCED_ORDERS';
    public const SUBSCRIPTION_DISCOUNT_FLAG = 'AMAZONPAY_SUBSCRIPTION_DISCOUNT';

    public const CRON_JOB_TYPE_SYNC = 'lpa_sync_cron';

    public const EVENT_AFTER_SET_SHIPPING_ADDRESS = 'amazon_pay.after_set_shipping_address';
    public const EVENT_AFTER_RESET_SESSION = 'amazon_pay.after_reset_session';
    public const EVENT_AFTER_SET_SHIPPING_PAYMENT_METHOD = 'amazon_pay.after_set_shipping_payment_method';
    public const EVENT_PREPARE_PAYMENT_PROCESS = 'amazon_pay.prepare_payment_process';

    public const EVENT_HANDLE_RESULT_START = 'amazon_pay.handle_result_start';

    public const SUBSCRIPTION_ORDERATTRIBUTE_FLAG_NEW = 'new';

    public const MAIL_TEMPLATE_SUBSCRIPTION_REMINDER = 'amazonpaysubreminder';
    public const MAIL_TEMPLATE_SUBSCRIPTION_STARTED = 'amazonpaysubstarted';
    public const MAIL_TEMPLATE_SUBSCRIPTION_STOPPED = 'amazonpaysubstopped';

    public const SUBSCRIPTION_EXCEPTION_CODE_GENERIC = 1;
    public const SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE = 2;
    public const SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE_PERMISSION = 3;
    public const SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DOES_NOT_EXIST = 4;
    public const SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DEACTIVATED = 5;
    public const SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_STOCK_LEVELS = 6;
    public const SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE = 7;


    public const TEST_REFERENCE_ID = 'S00-0000000-0000000';
}