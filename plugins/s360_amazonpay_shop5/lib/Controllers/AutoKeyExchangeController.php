<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JsonException;
use JTL\Plugin\PluginInterface;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;

/**
 * Class AutoKeyExchangeController
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class AutoKeyExchangeController {

    use JtlLoggerTrait;

    private const RESULT_ERROR = 'error';
    private const RESULT_SUCCESS = 'success';
    private const ALLOWED_ORIGINS = [
        'https://payments.amazon.com',
        'https://payments-eu.amazon.com',
        'https://sellercentral.amazon.com',
        'https://sellercentral-europe.amazon.com'
    ];

    public function __construct() {
    }

    public function handle(): void {
        try {

            if(empty($_REQUEST)) {
                $this->debugLog('Auto Key Exchange-Controller: Called with empty request. Cannot proceed.', __CLASS__);
                $this->finalize(400);
                return;
            }

            /*
             * Intercept test calls from browsers.
             */
            if (isset($_GET['lpacheck'])) {
                $this->finalize(200, 'Auto Key Exchange is reachable.');
                return;
            }

            /*
             * Intercept preflight requests
             */
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                $this->debugLog('Auto Key Exchange-Controller: Preflight request received.', __CLASS__);
                $this->respondToPreflight();
                return;
            }

            // Check auth token
            if(empty($_GET['auth']) || $_GET['auth'] !== Config::getInstance()->getKeyExchangeToken()) {
                $this->finalize(400, 'Invalid/missing token.');
                return;
            }

            // Check origin header.
            if(!isset($_SERVER['HTTP_ORIGIN']) || !\in_array($_SERVER['HTTP_ORIGIN'], self::ALLOWED_ORIGINS)) {
                $this->finalize(400, 'Invalid origin.');
                return;
            }

            if(defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
                $this->debugLog('Auto Key Exchange-Controller: Raw data received: ' . print_r($_REQUEST, true), __CLASS__);
            } else {
                $this->debugLog('Auto Key Exchange-Controller: Callback received.', __CLASS__);
            }


            if(empty($_REQUEST['payload'])) {
                $this->debugLog('Auto Key Exchange-Controller: Called with missing payload parameter. Cannot proceed.', __CLASS__);
                $this->finalize(400);
                return;
            }

            $payload = json_decode($_REQUEST['payload'], true, 510, JSON_THROW_ON_ERROR);

            $publicKeyId = $payload['publicKeyId'] ?? null;
            $merchantId = $payload['merchantId'] ?? null;
            $storeId = $payload['storeId'] ?? null;

            if(empty($publicKeyId)) {
                $this->debugLog('Auto Key Exchange-Controller: Called with missing public key id. Cannot proceed.', __CLASS__);
                $this->finalize(400);
                return;
            }

            $publicKeyId = urldecode($publicKeyId);
            $decryptedPublicKeyId = null;
            /** @noinspection PhpComposerExtensionStubsInspection */
            $success = openssl_private_decrypt(
                base64_decode($publicKeyId),
                $decryptedPublicKeyId,
                Config::getInstance()->getPrivateKey()
            );

            if($success && $this->isValidPublicKeyId($decryptedPublicKeyId)) {
                $this->debugLog('Auto Key Exchange-Controller: Callback successful. Saving received configuration data.', __CLASS__);
                Config::getInstance()->setMerchantId($merchantId ?? '');
                Config::getInstance()->setClientId($storeId ?? '');
                Config::getInstance()->setPublicKeyId($decryptedPublicKeyId ?? '');
                Config::getInstance()->setRegion(Config::REGION_DE); // This is not received but we can reasonably default to this.
                $this->finalize(200);
                return;
            }
            $this->debugLog('Auto Key Exchange-Controller: Callback NOT successful. Received data is invalid or wrong key was used for encryption.', __CLASS__);
            $this->finalize(400, 'Invalid payload.');
            return;

        } catch (JsonException $ex) {
            $this->debugLog('Auto Key Exchange-Controller: Failed to parse JSON. Exception: ' . $ex->getCode() . ' with message "' . $ex->getMessage() . '"', __CLASS__);
            $this->finalize(400, 'Failed to parse JSON.');
            return;
        } catch (Exception $ex) {
            $this->debugLog('Auto Key Exchange-Controller: Exception ' . $ex->getCode() . ' with message "' . $ex->getMessage() . '"', __CLASS__);
            $this->finalize(400, 'Internal exception - check shop log for details.');
            return;
        }
    }


    private function finalize(int $status, string $errorMessage = ''): void {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . '"');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
        header('Vary: Origin');
        header('Content-Type: application/json');
        $result = [];
        switch ($status) {
            case 200:
                http_response_code(200);
                $result['result'] = self::RESULT_SUCCESS;
                break;
            case 400:
                http_response_code(400);
                $result['result'] = self::RESULT_ERROR;
                $result['message'] = $errorMessage;
                break;
        }
        try {
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch(Exception $ex) {
            http_response_code(400);
            /** @noinspection JsonEncodingApiUsageInspection */
            echo json_encode([
                'result' => 'error',
                'messages' => 'Failed to properly encode response.'
            ]);
        }
        exit();
    }

    private function respondToPreflight(): void {
        // There are 4 allowed origins, we can only set one, so we mirror the one given.
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . '"');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
        header('Vary: Origin');
        exit();
    }

    private function isValidPublicKeyId($publicKeyId): bool {
        return !empty($publicKeyId)
            && mb_strlen($publicKeyId) < 100
            && preg_replace('/\W/', '', $publicKeyId) === $publicKeyId;
    }
}