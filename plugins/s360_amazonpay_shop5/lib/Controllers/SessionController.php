<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Session\Frontend;
use JTL\Shop;

/**
 * Class SessionController
 *
 * Used for all session operations.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class SessionController {

    private const BASE_KEY = 'amazonPay';

    public const KEY_ACCESS_TOKEN = 'accessToken';
    public const KEY_CONTEXT = 'context';
    public const KEY_CUSTOMER_TARGET_LOCATION = 'customerTargetLocation';
    public const KEY_FORCE_LOGOUT = 'forceLogout';
    public const KEY_USER_INFO = 'userInfo';
    public const KEY_CONFIRM_POST_ARRAY = 'confirmPostArray';
    public const KEY_CART_CHECKSUM = 'cartChecksum';
    public const KEY_PROCESSING_ERROR_SOFT_DECLINE = 'processingErrorSoftDecline';
    protected const KEY_CHECKOUT_SESSION = 'checkoutSession'; // protected to prevent accidental access without using the proper function
    protected const KEY_CHECKOUT_SESSION_RECURRING = 'checkoutSessionRecurring'; // protected to prevent accidental access without using the proper function
    public const KEY_APB_ORDER = 'apb_order';
    public const KEY_SUBSCRIPTION_SELECTED_INTERVAL = 'subscriptionSelectedInterval';

    public static function set(string $key, $value): void {
        if(!isset($_SESSION[self::BASE_KEY])) {
            $_SESSION[self::BASE_KEY] = [];
        }
        $_SESSION[self::BASE_KEY][$key] = $value;
    }

    public static function get(string $key) {
        if(!isset($_SESSION[self::BASE_KEY])) {
            return null;
        }
        if(isset($_SESSION[self::BASE_KEY][$key])) {
            return $_SESSION[self::BASE_KEY][$key];
        }
        return null;
    }

    public static function has(string $key): bool {
        return self::get($key) !== null;
    }

    public static function clear(string $key): void {
        if(isset($_SESSION[self::BASE_KEY][$key])) {
            unset($_SESSION[self::BASE_KEY][$key]);
        }
    }

    public static function clearAll(): void {
        if(isset($_SESSION[self::BASE_KEY])) {
            unset($_SESSION[self::BASE_KEY]);
        }
    }

    public static function isAdminLoggedIn(): bool {
        return Shop::isAdmin();
    }

    public static function hasDownloadProducts() {
        $cart = Frontend::getCart();
        if (empty($cart)) {
            return false;
        }
        if (\count($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $oPosition) {
                if ((int) $oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                    if (isset($oPosition->Artikel->oDownload_arr) && \count($oPosition->Artikel->oDownload_arr) > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public static function hasOnlyDownloadProducts(): bool {
        $cart = Frontend::getCart();
        if (empty($cart)) {
            return false;
        }
        if (\is_array($cart->PositionenArr) > 0) {
            foreach ($cart->PositionenArr as $oPosition) {
                if ((int) $oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                    if (!\is_array($oPosition->Artikel->oDownload_arr) || \count($oPosition->Artikel->oDownload_arr) === 0) {
                        return false;
                    }
                }
            }
        }
        // Return true only if we also have at least one download product
        return self::hasDownloadProducts();
    }

    public static function getActiveCheckoutSession() {
        if(self::get(self::KEY_SUBSCRIPTION_SELECTED_INTERVAL) !== null) {
            return self::get(self::KEY_CHECKOUT_SESSION_RECURRING);
        }
        return self::get(self::KEY_CHECKOUT_SESSION);
    }

    public static function setActiveCheckoutSession($checkoutSession): void {
        if(self::get(self::KEY_SUBSCRIPTION_SELECTED_INTERVAL) !== null) {
            self::set(self::KEY_CHECKOUT_SESSION_RECURRING, $checkoutSession);
        }
        self::set(self::KEY_CHECKOUT_SESSION, $checkoutSession);
    }

    public static function clearActiveCheckoutSession(): void {
        if(self::get(self::KEY_SUBSCRIPTION_SELECTED_INTERVAL) !== null) {
            self::clear(self::KEY_CHECKOUT_SESSION_RECURRING);
        }
        self::clear(self::KEY_CHECKOUT_SESSION);
    }

    public static function clearAllCheckoutSessions(): void {
        self::clear(self::KEY_CHECKOUT_SESSION);
        self::clear(self::KEY_CHECKOUT_SESSION_RECURRING);
        self::clear(self::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
    }

}