<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\Adapter\OAuthAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\Frontend\AccessToken;
use Plugin\s360_amazonpay_shop5\lib\Frontend\Button;
use Plugin\s360_amazonpay_shop5\lib\Frontend\UserInfo;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Buyer;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetBuyer;

/**
 * Class ReturnController
 *
 * Controls return of the customer after logging in via Amazon Pay.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class ReturnController {

    use JtlLoggerTrait;

    private const AUTH_MODE_CODE = 'code'; // This is when we get an auth code from Amazon Login
    private const AUTH_MODE_TOKEN = 'token'; // This is when we get an access token from Amazon Login
    private const AUTH_MODE_UNKNOWN = 'unknown'; // This is when we get nothing
    private const AUTH_MODE_ERROR = 'error'; // This is when Amazon Login returns an error
    private const AUTH_MODE_CHECKOUT = 'checkout'; // This is when we came here from an initiated checkout session from Amazon Pay
    private const AUTH_MODE_BUYER_TOKEN = 'buyerToken'; // This is when the user logged in (purely, no pay!) with Amazon Pay Checkout v2

    private $authCode;
    private $accessToken;
    private $accessTokenExpiresIn;
    private $buyerToken;
    private $checkoutSessionId;
    private $state;
    private $customerLanguageId;
    private $customerLanguageIso;
    private $mode;
    private $error;

    /**
     * ReturnController constructor.
     */
    public function __construct() {
        if (isset($_REQUEST['access_token'])) {
            $this->accessToken = urldecode($_REQUEST['access_token']);
        }
        $this->accessTokenExpiresIn = 0;
        if (isset($_REQUEST['expires_in'])) {
            $this->accessTokenExpiresIn = (int)$_REQUEST['expires_in'];
        }
        if (isset($_REQUEST['state'])) {
            $this->state = json_decode(html_entity_decode(urldecode($_REQUEST['state']), ENT_COMPAT | ENT_HTML5, 'UTF-8'), true);
        }
        if (isset($_REQUEST['code'])) {
            $this->authCode = $_REQUEST['code'];
        }
        if(isset($_REQUEST['amazonCheckoutSessionId'])) {
            $this->checkoutSessionId = Text::filterXSS($_REQUEST['amazonCheckoutSessionId']);
        }
        if (isset($_REQUEST['buyerToken'])) {
            $this->buyerToken = $_REQUEST['buyerToken'];
        }
        $this->mode = self::AUTH_MODE_UNKNOWN;
        if (!empty($this->authCode)) {
            // This mode is currently not enabled
            $this->mode = self::AUTH_MODE_CODE;
        } elseif (!empty($this->accessToken)) {
            $this->mode = self::AUTH_MODE_TOKEN;
        } elseif (!empty($this->checkoutSessionId)) {
            $this->mode = self::AUTH_MODE_CHECKOUT;
        } elseif (isset($_REQUEST['error'])) {
            $this->mode = self::AUTH_MODE_ERROR;
            $this->error = $_REQUEST['error'];
        } elseif (!empty($this->buyerToken)) {
            $this->mode = self::AUTH_MODE_BUYER_TOKEN;
        }
    }

    /**
     * Do the actual handling.
     * This method always ends in a redirection!
     */
    public function handle(): void {

        // Avoid ajax calls to this site altogether
        if(Request::isAjaxRequest()) {
            return;
        }

        try {
            $this->debugLog('ReturnController started handling of request in mode "' . $this->mode . '".', __CLASS__);
            $this->debugLog('Checking for valid request data.', __CLASS__);
            if ($this->mode === self::AUTH_MODE_UNKNOWN) {
                /*
                 * We might be in the "first" return and the access token is contained in the URI fragment. We have to wait for our JS to take over and remap the access token to our GET parameters instead.
                 * We do this by signaling to Hook 140 that we need our return.tpl rendered into the <head> such that it immediately executes before rendering this page.
                 */
                // MAYBE: add an error message - since this is never displayed if everything goes well, we add it as a precaution
                Shop::set('lpaIsReturnRequired', true);
                // We cannot continue doing anything useful here.
                return;
            }

            if($this->mode === self::AUTH_MODE_ERROR) {
                // Amazon returned with an error. Display it. Return user to home page.
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso) . " ({$this->error})", 'lpaAmazonErrorCode', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToHome();
            }

            if($this->mode === self::AUTH_MODE_CHECKOUT) {
                // The user came here from Amazon Pay login/checkout creation
                // In this case we get his ID, name and email (= userInfo) from the APIV2
                // NOTE: We do not get a language back in this case, so we have to rely on having given the user the correct language return url, before
                $apiAdapter = new ApiAdapter();
                $getCheckoutSessionRequest = new GetCheckoutSession($this->checkoutSessionId);
                $response = $apiAdapter->execute($getCheckoutSessionRequest);
                if($response instanceof CheckoutSession) {
                    /** @var CheckoutSession $response */
                    
                    /*
                     * Check if the buyer is set - this might not be the case if the user directly aborted the checkout.
                     */
                    if($response->getBuyer() === null) {
                        $this->debugLog('Checkout Session in response does not contain a buyer - assuming abort by customer and redirecting to previous location without further handling.');
                        $this->redirectToPreviousLocation();
                    }
                    
                    $userInfo = new UserInfo();
                    $userInfo->setEmail($response->getBuyer()->getEmail());
                    $userInfo->setUserId($response->getBuyer()->getBuyerId());
                    $userInfo->setName($response->getBuyer()->getName());
                    SessionController::setActiveCheckoutSession($response);
                    SessionController::set(SessionController::KEY_USER_INFO, $userInfo);
                    SessionController::set(SessionController::KEY_CONTEXT, Button::CONTEXT_PAY_GLOBAL); // at this point, we default to pay context (we do not care at this point, if the button was pressed from express/category/cart)
                    $this->forwardCustomerToLogin(JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CHECKOUT));
                } else {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC), 'lpaErrorOnGetCheckoutSession', ['dismissable' => true, 'saveInSession' => true]);
                    $this->redirectToPreviousLocation();
                }
            }

            if ($this->mode === self::AUTH_MODE_BUYER_TOKEN) {
                // The user logged in with Amazon Pay APIv2 Product "SignIn". We get his user data from the API
                $apiAdapter = new ApiAdapter();
                $getBuyerRequest = new GetBuyer($this->buyerToken);
                $response = $apiAdapter->execute($getBuyerRequest);
                if ($response instanceof Buyer) {
                    /** @var Buyer $response */
                    $userInfo = new UserInfo();
                    $userInfo->setEmail($response->getEmail());
                    $userInfo->setUserId($response->getBuyerId());
                    $userInfo->setName($response->getName());
                    SessionController::set(SessionController::KEY_USER_INFO, $userInfo);
                    SessionController::set(SessionController::KEY_CONTEXT, Button::CONTEXT_LOGIN);
                    $this->forwardCustomerToLogin();
                } else {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC), 'lpaErrorOnGetBuyer', ['dismissable' => true, 'saveInSession' => true]);
                    $this->redirectToPreviousLocation();
                }
            }

            // check if the state parameter was set
            if (null === $this->state) {
                $this->debugLog('Client returned without state information. Redirecting to home page.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaReturnErrorInfo', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToHome();
            }

            // check csrf token
            if (!isset($this->state['csrf']) || empty($this->state['csrf'])) {
                $this->debugLog('Failed due to missing csrf token. Redirecting to previous page.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaCsrfError', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToPreviousLocation();
            }
            if( !Shop::Container()->getCryptoService()->stableStringEquals($_SESSION['jtl_token'], $this->state['csrf'])) {
                $this->debugLog('Failed due to wrong csrf token. Redirecting to previous page.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaCsrfError', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToPreviousLocation();
            }

            $this->debugLog('CSRF Token confirmed.', __CLASS__);

            $oAuthAdapter = new OAuthAdapter();

            $accessToken = null;
            if($this->mode === self::AUTH_MODE_CODE) {
                // if we are in code grant mode, we need to exchange the auth code for an access token
                $this->debugLog('Getting accessToken for authCode.', __CLASS__);
                $accessToken = $oAuthAdapter->exchangeAuthorizationCodeForAccessToken($this->authCode);
            } else {
                $this->debugLog('Validating given accessToken.', __CLASS__);
                // if we are in token mode, we have to validate the access token, before continuing.
                if (!$oAuthAdapter->validateAccessToken($this->accessToken)) {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaInvalidAccessToken', ['dismissable' => true, 'saveInSession' => true]);
                    $this->redirectToPreviousLocation();
                }
                $this->debugLog('AccessToken is valid. Getting user information.', __CLASS__);

                $accessToken = new AccessToken();
                $accessToken->setAccessToken($this->accessToken)
                    ->setCreationTimestamp(time())
                    ->setExpiresIn($this->accessTokenExpiresIn ?? 0);
            }


            if($accessToken === null) {
                // something went wrong
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaReturnErrorInfo', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToPreviousLocation();
            }

            // make sure we will redirect the customer to his original language
            $language = LanguageHelper::getLangIDFromIso($this->state['lang']);
            $this->customerLanguageId = $language !== null ? (int)$language->kSprachISO : 0;
            $this->customerLanguageIso = $language !== null ? $language->cISO : '';


            // now that we have an access token we can get the actual profile information
            $userInfo = $oAuthAdapter->getUserInfo($accessToken);
            if (null === $userInfo) {
                $this->debugLog('Failed to get user info. Redirecting to previous page.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaReturnErrorInfo', ['dismissable' => true, 'saveInSession' => true]);
                $this->redirectToPreviousLocation();
            }

            // set the access token and user data in the session
            SessionController::set(SessionController::KEY_ACCESS_TOKEN, $accessToken);
            SessionController::set(SessionController::KEY_USER_INFO, $userInfo);
            SessionController::set(SessionController::KEY_CONTEXT, $this->state['context']);

            $this->debugLog('oAuth2 user info data received. Redirecting customer to next page.', __CLASS__);
            // we retrieved the oAuth2 stuff, lets forward the user to where we want him to go.
            switch ($this->state['context']) {
                case Button::CONTEXT_LOGIN:
                    $this->forwardCustomerToLogin($this->state['location']);
                    break;
                case Button::CONTEXT_PAY_CATEGORY:
                case Button::CONTEXT_PAY_GLOBAL:
                case Button::CONTEXT_PAY_DETAIL:
                    $this->forwardCustomerToLogin(JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CHECKOUT, $this->customerLanguageId));
                    break;
                default:
                    $this->redirectToPreviousLocation();
                    break;
            }
        } catch (Exception $e) {
            $this->errorLog($e->getMessage(), __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC, $this->customerLanguageIso), 'lpaReturnErrorInfo', ['dismissable' => true, 'saveInSession' => true]);
            $this->redirectToPreviousLocation();
        }
    }

    private function redirectToHome(): void {
        header('Location: ' . Shop::getURL(true));
        exit();
    }

    private function redirectToPreviousLocation(): void {
        if (isset($this->state['location'])) {
            header('Location: ' . $this->state['location']);
            exit();
        }
        $this->redirectToHome();
    }

    /**
     * @param null $redirectTo - URL to redirect the customer to after successful login
     */
    private function forwardCustomerToLogin($redirectTo = null): void {
        // the login needs to know where the customer must be redirected to after his login
        if (!empty($redirectTo)) {
            SessionController::set(SessionController::KEY_CUSTOMER_TARGET_LOCATION, $redirectTo);
        } else {
            SessionController::set(SessionController::KEY_CUSTOMER_TARGET_LOCATION, Shop::getURL(true));
        }

        header('Location: ' . JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_LOGIN, $this->customerLanguageId ?? 0));
        exit();
    }

}