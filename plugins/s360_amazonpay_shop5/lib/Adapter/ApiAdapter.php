<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Adapter;

use Amazon\Pay\API\Client;
use Amazon\Pay\API\ClientInterface;
use JTL\Plugin\PluginInterface;
use phpseclib\Crypt\RSA as RSASecLib2;
use phpseclib3\Crypt\RSA as RSASecLib3;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\AbstractOperation;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use ReflectionMethod;

require_once __DIR__ . '/ApiV2SDK/vendor/autoload.php';

/**
 * Class ApiAdapter
 *
 * Adapter to the APIV2 of Amazon Pay.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Adapter
 */
class ApiAdapter {

    use JtlLoggerTrait;

    /**
     * @var ClientInterface $client ;
     */
    protected $client;

    /**
     * Mandatory region for the APIV2 Client.
     * @var string $region
     */
    protected $region;

    /**
     * Mandatory flag if sandbox is active.
     * @var bool $sandbox
     */
    protected $sandbox;

    /**
     * The private RSA key to sign requests.
     * @var string $privateKey
     */
    protected $privateKey;

    /**
     * The key ID for the public key as provided by Amazon Pay.
     * @var string $publicKeyId
     */
    protected $publicKeyId;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var PluginInterface|null
     */
    protected $plugin;

    /**
     * Request internal cache to prevent computing the same signature twice for the same payload as
     * signature calculation is costly.
     *
     * @var array $payloadSignatures
     */
    protected $payloadSignatures = [];

    public function __construct() {
        $this->config = Config::getInstance();
        $this->plugin = Plugin::getInstance();
        $this->sandbox = $this->config->isSandbox();
        $this->region = $this->config->getRegion();
        $this->privateKey = $this->config->getPrivateKey() ?? '';
        $this->publicKeyId = $this->config->getPublicKeyId();
        $this->client = new Client([
            'sandbox' => $this->sandbox,
            'region' => $this->region,
            'private_key' => $this->privateKey,
            'public_key_id' => $this->publicKeyId,
            'integrator_id' => $this->config->getPlatformId(),
            'integrator_version' => $this->plugin->getCurrentVersion()->getOriginalVersion(),
            'platform_version' => APPLICATION_VERSION
        ]);
    }

    /**
     * Caution: Can be used to override client settings.
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client): void {
        $this->client = $client;
    }


    /**
     * Executes the operation via the client and returns an AmazonPay Object
     * @param AbstractOperation $operation
     * @return null|AbstractObject
     * @throws \Exception
     */
    public function execute(AbstractOperation $operation): ?AbstractObject {

        $headers = $operation->getHeaders();
        $objectId = $operation->getObjectId();
        $payload = $operation->getPayload();
        $operationName = $operation->getOperationName();
        $sandboxConfigOverridden = false;
        // Check if operation forces us into a specific mode - this may or may not be the globally configured mode. We reset this after the request.
        if($operation->getSandbox() !== null) {
            $sandboxConfigOverridden = true;
            $this->client->setSandbox($operation->getSandbox());
        }
        if(defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
            $this->debugLog('Performing request for Operation "' . $operationName . '" ' . print_r([
                    'headers' => $headers,
                    'objectId' => $objectId,
                    'payload' => $payload
                ], true), __CLASS__);
        } else {
            $this->debugLog('Performing request for Operation "' . $operationName . '"', __CLASS__);
        }

        try {
            switch ($operationName) {
                case 'cancelCharge':
                    $response = $this->client->cancelCharge($objectId, $payload);
                    break;
                case 'captureCharge':
                    $response = $this->client->captureCharge($objectId, $payload, $headers);
                    break;
                case 'closeChargePermission':
                    $response = $this->client->closeChargePermission($objectId, $payload);
                    break;
                case 'completeCheckoutSession':
                    $response = $this->client->completeCheckoutSession($objectId, $payload, $headers);
                    break;
                case 'createCharge':
                    $response = $this->client->createCharge($payload, $headers);
                    break;
                case 'createCheckoutSession':
                    $response = $this->client->createCheckoutSession($payload, $headers);
                    break;
                case 'createRefund':
                    $response = $this->client->createRefund($payload, $headers);
                    break;
                case 'getBuyer':
                    $response = $this->client->getBuyer($objectId);
                    break;
                case 'getCharge':
                    $response = $this->client->getCharge($objectId);
                    break;
                case 'getChargePermission':
                    $response = $this->client->getChargePermission($objectId);
                    break;
                case 'getCheckoutSession':
                    $response = $this->client->getCheckoutSession($objectId);
                    break;
                case 'getRefund':
                    $response = $this->client->getRefund($objectId);
                    break;
                case 'updateChargePermission':
                    $response = $this->client->updateChargePermission($objectId, $payload);
                    break;
                case 'updateCheckoutSession':
                    $response = $this->client->updateCheckoutSession($objectId, $payload);
                    break;
                case 'deliveryTrackers':
                    $response = $this->client->deliveryTrackers($payload);
                    break;
                default:
                    $this->errorLog('Tried to execute unrecognized operation "' . $operationName . '"', __CLASS__);
                    return null;
            }
            if(defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
                $this->debugLog('Response: ' . print_r($response, true), __CLASS__);
            } else {
                $this->debugLog('Response received.', __CLASS__);
            }
            return $this->handleResponse($operation, $response);
        } catch (\Exception $e) {
            $this->errorLog('Exception while trying to execute operation "' . $operationName . '": ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __CLASS__);
            throw $e;
        } finally {
            // if we did override the sandbox config, reset it to prevent follow-up errors on subsequent calls
            if($sandboxConfigOverridden) {
                $this->client->setSandbox($this->config->isSandbox());
            }
        }
    }

    /**
     * Handles the response in context of the given operation to return an appropriate result object.
     * Note that the result may also be an Error object!
     *
     * The response is an assoc array with the following structure:
     *
     * $response = array(
     * 'status'     => $statusCode, -> the http status code
     * 'method'     => $method, -> the http method used
     * 'url'        => $url, -> the requested url
     * 'headers'    => $postSignedHeaders, -> all headers sent after signing them
     * 'request'    => $payload, -> the original payload of the request
     * 'response'   => $response, -> the actual response payload/object data
     * 'request_id' => $this->requestId, -> the generated request id
     * 'retries'    => $retries, -> the number of retries performed
     * 'duration'   => intval(round((microtime(true)-$curtime) * 1000)) -> the request duration in milliseconds
     * );
     *
     *
     * @param AbstractOperation $operation
     * @param $response
     * @return AbstractObject
     */
    private function handleResponse(AbstractOperation $operation, $response) :AbstractObject {
        $httpStatusCode = (int) $response['status'];
        // on success, let the operation create its response object
        if($httpStatusCode === 200 || $httpStatusCode === 201 || $httpStatusCode === 202) {
            $responseDecoded = json_decode($response['response'], true);
            if($responseDecoded === null) {
                return new Error($httpStatusCode, Error::REASON_CODE_JSON_DECODE_FAILED, json_last_error_msg());
            }
            return $operation->createObjectFromResponse(json_decode($response['response'], true));
        }
        // else, we create an error
        $responseDecoded = json_decode($response['response'], true);
        $reasonCode = (null !== $responseDecoded) ? $responseDecoded['reasonCode'] : null;
        $message = (null !== $responseDecoded && isset($responseDecoded['message'])) ? $responseDecoded['message'] : '';
        if(empty($reasonCode)) {
            $reasonCode = Error::REASON_CODE_UNKNOWN;
        }
        return new Error($httpStatusCode, $reasonCode, $message);
    }

    /**
     * Creates a private/public key pair via the same RSA library that the Amazon Pay Client uses for signing.
     * We should avoid possible compatibility issues this way.
     *
     * Note: as we do not set a timeout, we should never just get a partialkey.
     *
     * Returns an array with the following three elements: (Same as the RSA class!)
     *  - 'privatekey': The private key.
     *  - 'publickey':  The public key.
     *  - 'partialkey': A partially computed key (if the execution time exceeded $timeout).
     *                  Will need to be passed back to \phpseclib\Crypt\RSA::createKey() as the third parameter for further processing.
     */
    public function createRSAKey($bits = 2048): array {

        /**
         * Depending on in which shop system version we are, there might be different RSA classes loaded with different names.
         * To be on the safe side, we check both namespaces (phpseclib and phpseclib3) and also if the createKey method is static or not (in phpseclib < 3 it is not static, in phpseclib 3 it is.)
         */
        $rsa = null;
        if(class_exists(RSASecLib2::class)) {
            $methodChecker = new ReflectionMethod(RSASecLib2::class,'createKey');
            if($methodChecker->isStatic()) {
                $privateKey = RSASecLib2::createKey($bits);
                $publicKey = $privateKey->getPublicKey();
                return [
                    'privatekey' => $privateKey->toString('PKCS1'),
                    'publickey' => $publicKey->toString('PKCS1'),
                    'partialkey' => ''
                ];
            }
            $rsa = new RSASecLib2();
            return $rsa->createKey($bits);
        } else if(class_exists(RSASecLib3::class)) {
            $methodChecker = new ReflectionMethod(RSASecLib3::class,'createKey');
            if($methodChecker->isStatic()) {
                $privateKey = RSASecLib3::createKey($bits);
                $publicKey = $privateKey->getPublicKey();
                return [
                    'privatekey' => $privateKey->toString('PKCS1'),
                    'publickey' => $publicKey->toString('PKCS1'),
                    'partialkey' => ''
                ];
            }
            $rsa = new RSASecLib3();
            return $rsa->createKey($bits);
        }
        return [
            'privatekey' => '',
            'publickey' => '',
            'partialkey' => ''
        ];
    }


    /**
     * Signs a given payload.
     * Make sure to give the payload as stringified JSON to ensure congruent signing.
     *
     * @param string $payload
     * @return string - the signature created
     */
    public function signPayload($payload): string {
        if(empty($payload)) {
            return '';
        }
        $payloadKey = md5($payload);
        if(!array_key_exists($payloadKey, $this->payloadSignatures)) {
            $this->payloadSignatures[$payloadKey] = $this->client->generateButtonSignature($payload);
        }
        return $this->payloadSignatures[$payloadKey];
    }

    // Only used for testing purposes.
    public function setSandbox(bool $sandbox) {
        $this->client->setSandbox($sandbox);
    }
}