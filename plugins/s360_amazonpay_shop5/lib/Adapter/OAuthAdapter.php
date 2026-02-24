<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Adapter;

use Plugin\s360_amazonpay_shop5\lib\Frontend\AccessToken;
use Plugin\s360_amazonpay_shop5\lib\Frontend\UserInfo;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;

/**
 * Class OAuthAdapter
 *
 * Used to technically acquire tokens for access codes and profile information.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Adapter
 */
class OAuthAdapter {

    use JtlLoggerTrait;

    private $config;

    private const AMAZON_OAUTH_TOKEN_ENDPOINT = 'https://api.amazon.com/auth/o2/token';
    private const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    private const GRANT_TYPE_TOKEN = 'token';
    private const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    private const PROFILE_ENDPOINTS = [
        Config::ENVIRONMENT_SANDBOX => [
            Config::REGION_DE => 'https://api.sandbox.amazon.de/user/profile',
            Config::REGION_UK => 'https://api.sandbox.amazon.co.uk/user/profile',
            Config::REGION_US => 'https://api.sandbox.amazon.com/user/profile',
            Config::REGION_JP => 'https://api.sandbox.amazon.co.jp/user/profile'
        ],
        Config::ENVIRONMENT_PRODUCTION => [
            Config::REGION_DE => 'https://api.amazon.de/user/profile',
            Config::REGION_UK => 'https://api.amazon.co.uk/user/profile',
            Config::REGION_US => 'https://api.amazon.com/user/profile',
            Config::REGION_JP => 'https://api.amazon.co.jp/user/profile'
        ]
    ];

    private const TOKEN_VALIDATION_ENDPOINTS = [
        Config::ENVIRONMENT_SANDBOX => [
            Config::REGION_DE => 'https://api.sandbox.amazon.de/auth/o2/tokeninfo?access_token=',
            Config::REGION_UK => 'https://api.sandbox.amazon.co.uk/auth/o2/tokeninfo?access_token=',
            Config::REGION_US => 'https://api.sandbox.amazon.com/auth/o2/tokeninfo?access_token=',
            Config::REGION_JP => 'https://api.sandbox.amazon.co.jp/auth/o2/tokeninfo?access_token=',
        ],
        Config::ENVIRONMENT_PRODUCTION => [
            Config::REGION_DE => 'https://api.amazon.de/auth/o2/tokeninfo?access_token=',
            Config::REGION_UK => 'https://api.amazon.co.uk/auth/o2/tokeninfo?access_token=',
            Config::REGION_US => 'https://api.amazon.com/auth/o2/tokeninfo?access_token=',
            Config::REGION_JP => 'https://api.amazon.co.jp/auth/o2/tokeninfo?access_token=',
        ]
    ];


    public function __construct() {
        $this->config = Config::getInstance();
    }

    /**
     * Exchanges an auth code for an access token.
     *
     * Example request:
     *
     * POST /auth/o2/token HTTP/1.1
     * Host: api.amazon.com
     * Content-Type: application/x-www-form-urlencoded;charset=UTF-8
     *
     * grant_type=authorization_code
     * &code=SplxlOBezQQYbYS6WxSbIA
     * &client_id=foodev
     * &client_secret=Y76SDl2F
     *
     * @param $authCode
     * @return null|AccessToken
     */
    public function exchangeAuthorizationCodeForAccessToken($authCode): ?AccessToken {
        $this->debugLog('Trying to get access token for authorization code "'.$authCode.'"', __CLASS__);
        $clientId = $this->config->getClientId();
        $clientSecret = $this->config->getClientSecret();

        if (empty($clientId) || empty($clientSecret)) {
            $this->errorLog('Client-ID or Client-Secret is not configured. Cannot perform exchange auth code for access token.', __CLASS__);
            return null;
        }


        $parameters = 'grant_type=' . self::GRANT_TYPE_AUTHORIZATION_CODE . '&code=' . $authCode . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&redirect_uri=' . JtlLinkHelper::getInstance()->getFullReturnUrl();
        $ch = curl_init(self::AMAZON_OAUTH_TOKEN_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        if (!empty($curlError)) {
            // error in the curl call
            $this->errorLog('Curl-Error when requesting access token: ' . $curlError, __CLASS__);
        }
        curl_close($ch);
        return $this->handleAccessTokenResponse($response);
    }

    /**
     * Uses an existing access token's refresh token to get a new (refreshed) access token without
     * requiring the user to authenticate again.
     *
     * @param AccessToken $accessToken
     * @return null|AccessToken
     */
    public function refreshAccessToken(AccessToken $accessToken): ?AccessToken {
        $clientId = $this->config->getClientId();
        $clientSecret = $this->config->getClientSecret();

        if (empty($clientId) || empty($clientSecret)) {
            $this->errorLog('Client-ID or Client-Secret is not configured. Cannot perform refresh for access token.', __CLASS__);
            return null;
        }

        $parameters = 'grant_type=' . self::GRANT_TYPE_REFRESH_TOKEN . '&refresh_token=' . $accessToken->getRefreshToken() . '&client_id=' . $clientId . '&client_secret=' . $clientSecret . '&redirect_uri=' . JtlLinkHelper::getInstance()->getFullReturnUrl();
        $ch = curl_init(self::AMAZON_OAUTH_TOKEN_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        if (!empty($curlError)) {
            // error in the curl call
            $this->errorLog('Curl-Error when requesting access token: ' . $curlError, __CLASS__);
        }
        curl_close($ch);
        return $this->handleAccessTokenResponse($response);
    }

    private function handleAccessTokenResponse($response): ?AccessToken {
        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            // the access token is missing
            $this->debugLog('No access token received. ' . json_encode($data), __CLASS__);
            return null;
        }
        $accessToken = new AccessToken();
        $accessToken->setAccessToken($data['access_token'])
            ->setCreationTimestamp(time())
            ->setExpiresIn($data['expires_in'] ?? 0)
            ->setRefreshToken($data['refresh_token'] ?? '')
            ->setTokenType($data['token_type'] ?? 'bearer');
        return $accessToken;
    }

    /**
     * Gets the user info from the correct endpoint.
     * @param $accessToken
     * @return null|UserInfo
     */
    public function getUserInfo(AccessToken $accessToken): ?UserInfo {

        // exchange the access token for user profile
        $ch = curl_init(self::PROFILE_ENDPOINTS[$this->config->getEnvironment()][$this->config->getRegion()]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $accessToken->getAccessToken()));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        if (!empty($curlError)) {
            // error in the curl call
            $this->errorLog('Curl-Error when requesting customer profile information: ' . $curlError, __CLASS__);
        }
        curl_close($ch);
        $data = json_decode($response, true);
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            // the user id is missing
            $this->debugLog('No user id received. ' . json_encode($data), __CLASS__);
            return null;
        }
        if (!isset($data['email']) || empty($data['email'])) {
            // the email is missing
            $this->debugLog('No email address received. ' . json_encode($data), __CLASS__);
            return null;
        }
        $userInfo = new UserInfo();
        $userInfo->setUserId($data['user_id'])
            ->setEmail($data['email'])
            ->setName($data['name']);
        return $userInfo;
    }

    /**
     * Validates an access token against Amazon.
     * Validation is done by requesting information on the token and then comparing our client id to the id which generated the token.
     * @param string $accessToken
     * @return bool
     */
    public function validateAccessToken(string $accessToken): bool {
        $ch = curl_init(self::TOKEN_VALIDATION_ENDPOINTS[$this->config->getEnvironment()][$this->config->getRegion()] . urlencode($accessToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        if(!empty($curlError)) {
            $this->errorLog('Curl-Error when trying to validate token: ' . $curlError, __CLASS__);
            return false;
        }
        curl_close($ch);
        $data = json_decode($response, true);
        return isset($data['aud']) && $data['aud'] === $this->config->getClientId();
    }
}