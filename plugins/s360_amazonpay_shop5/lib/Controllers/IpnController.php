<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Plugin\PluginInterface;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\Adapter\IpnAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetRefund;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\StatusHandlerException;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;

/**
 * Class IpnController
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class IpnController {

    use JtlLoggerTrait;

    private const NOTIFICATION_TYPE_STATE_CHANGE = 'STATE_CHANGE'; // currently the only Notification Type

    private const OBJECT_TYPE_CHARGE = 'charge';
    private const OBJECT_TYPE_REFUND = 'refund';

    private const TEST_IPN_ID = 'P01-0000000-0000000-000000';

    /**
     * @var PluginInterface $plugin
     */
    private $requestBody;
    private $adapter;
    private $statusController;

    public function __construct() {

        $this->requestBody = file_get_contents('php://input');
        $this->adapter = new ApiAdapter();
        $this->statusController = new StatusController();
    }

    public function handle(): void {
        try {
            /*
             * Intercept test calls from browsers.
             */
            if (isset($_GET['lpacheck'])) {
                $this->finalize(200, 'IPN is reachable.');
            }

            if(defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
                $this->debugLog('IPN-Handler: IPN raw data received: ' . print_r($this->requestBody, true), __CLASS__);
            } else {
                $this->debugLog('IPN-Handler: IPN received', __CLASS__);
            }

            if(!IpnAdapter::validateIpnPost($this->requestBody)) {
                $this->debugLog('IPN-Handler: Invalid IPN received. Body could not be validated.', __CLASS__);
                $this->finalize(400);
            }

            /**
             * ATTENTION: An IPN is a JSON object that contains the actual message as NESTED JSON in the 'Message' field.
             */
            $ipn = json_decode($this->requestBody, true, 512, JSON_THROW_ON_ERROR);
            $ipn['Message'] = json_decode($ipn['Message'], true, 512, JSON_THROW_ON_ERROR);

            $this->debugLog('IPN-Handler: IPN after decode: ' . print_r($ipn, true), __CLASS__);

            if(empty($ipn['Message']['ObjectType'])) {
                $this->debugLog('IPN-Handler: Missing/empty ObjectType field in Message. Replying with 400 Bad Request.', __CLASS__);
                $this->finalize(400);
            }

            switch (strtolower($ipn['Message']['ObjectType'])) {
                case self::OBJECT_TYPE_CHARGE:
                    $chargeId = $ipn['Message']['ObjectId'];
                    $this->handleChargeNotification($chargeId);
                    break;
                case self::OBJECT_TYPE_REFUND:
                    $refundId = $ipn['Message']['ObjectId'];
                    $this->handleRefundNotification($refundId);
                    break;
                default:
                    $this->debugLog('IPN-Handler: Unrecognized ObjectType "' . $ipn['Message']['ObjectType'] . '". Replying with 400 Bad Request.', __CLASS__);
                    $this->finalize(400);
                    break;
            }
        } catch (StatusHandlerException $ex) {
            if ($ex->getCode() === StatusHandlerException::CODE_UNKNOWN_OBJECT) {
                // Unknown objects might be due to the IPN arriving "too early" - we have to reply with an error 500 to make Amazon try again.
                $this->finalize(503);
            } else {
                $this->finalize(500);
            }
        } catch (\Throwable $t) {
            $this->debugLog('IPN-Handler: Exception ' . $t->getCode() . ' with message "' . $t->getMessage() . '"', __CLASS__);
            $this->finalize(400);
        }
        $this->finalize(200);
    }

    private function handleChargeNotification($chargeId): void {
        $request = new GetCharge($chargeId);
        $response = $this->adapter->execute($request);
        if ($response instanceof Error) {
            /** @var Error $response */
            throw new TechnicalException($response->getReasonCode(), $response->getHttpErrorCode());
        }
        /** @var Charge $response */
        $this->statusController->handleCharge($response);

        // Also update the charge permission itself, because it never receives IPNs for itself.
        $this->handleChargePermission($response->getChargePermissionId());
    }

    private function handleRefundNotification($refundId): void {
        $request = new GetRefund($refundId);
        $response = $this->adapter->execute($request);
        if ($response instanceof Error) {
            /** @var Error $response */
            throw new TechnicalException($response->getReasonCode(), $response->getHttpErrorCode());
        }
        /** @var Refund $response */
        $this->statusController->handleRefund($response);

    }

    /**
     * Note that there are no charge permission IPNs - we need to manually update the charge permission after
     * reception of an IPN.
     *
     * This probably only applies for charge IPNs.
     *
     * @param $chargePermissionId
     * @throws TechnicalException
     */
    private function handleChargePermission($chargePermissionId) {
        $request = new GetChargePermission($chargePermissionId);
        $response = $this->adapter->execute($request);
        if ($response instanceof Error) {
            /** @var Error $response */
            throw new TechnicalException($response->getReasonCode(), $response->getHttpErrorCode());
        }
        /** @var ChargePermission $response */
        $this->statusController->handleChargePermission($response);
    }

    private function finalize(int $status, string $message = ''): void {
        switch ($status) {
            case 200:
                header('HTTP/1.1 200 OK');
                break;
            case 400:
                header('HTTP/1.1 400 Bad Request');
                break;
            case 404:
                header('HTTP/1.1 404 Not Found');
                break;
            case 500:
                header('HTTP/1.1 500 Internal Server Error');
                break;
            case 501:
                header('HTTP/1.1 501 Not Implemented');
                break;
            case 503:
                header('HTTP/1.1 503 Service Unavailable');
                break;
        }
        if (!empty($message)) {
            echo $message;
        }
        exit();
    }
}