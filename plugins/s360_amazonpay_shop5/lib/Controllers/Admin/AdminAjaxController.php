<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers\Admin;

use Exception;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CancelCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CaptureCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CloseChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateRefund;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\Controllers\StatusController;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SubscriptionController;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Crypto;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;

/**
 * Class AdminAjaxController
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers\Admin
 */
class AdminAjaxController extends AdminController {

    private const RESULT_ERROR = 'error';
    private const RESULT_FAIL = 'fail';
    private const RESULT_SUCCESS = 'success';
    private const RESULT_UNKNOWN = 'unknown';

    /**
     * @var Database $database
     */
    private $database;

    public function __construct(PluginInterface $plugin) {
        parent::__construct($plugin);
        $this->database = Database::getInstance();
        $this->request = Text::filterXSS($this->request);
    }


    /**
     * Expected to fill Smarty Variables and output the template.
     *
     * The ajax controller does not return a rendered smarty template and should not be called like this.
     *
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     */
    public function handle(): string {
        throw new MethodNotImplementedException(__CLASS__ . ':' . __FUNCTION__);
    }

    /**
     * Handles an ajax request.
     * This method must always return an array (which will be turned into json automatically).
     *
     * Basic structure of the array:
     *
     * [
     *  'result' => 'success'|'fail'|'error'|'unknown',
     *  'messages' => [...],
     *  'data' => [...]
     * ]
     *
     * @return array
     * @throws \Exception
     */
    public function handleAjax(): array {
        $action = $this->request['action'];
        $csrf = $this->request['csrf'];
        if(empty($csrf) || !Form::validateToken($csrf)) {
            throw new Exception("Missing or wrong CSRF token.");
        }
        switch ($action) {
            case 'checkAccess':
                return $this->handleCheckAccess();
            case 'createKey':
                return $this->handleCreateKey();
            case 'loadOrders':
                return $this->handleLoadOrders();
            case 'searchOrders':
                return $this->handleSearchOrders();
            case 'refreshChargePermission':
                return $this->handleRefreshChargePermission();
            case 'getChargePermission':
                return $this->handleGetChargePermission();
            case 'closeChargePermission':
                return $this->handleCloseChargePermission();
            case 'createCharge':
                return $this->handleCreateCharge();
            case 'captureCharge':
                return $this->handleCaptureCharge();
            case 'cancelCharge':
                return $this->handleCancelCharge();
            case 'createRefund':
                return $this->handleCreateRefund();
            case 'loadSubscriptions':
                return $this->handleLoadSubscriptions();
            case 'cancelSubscription':
                return $this->handleCancelSubscription();
            case 'createOrderForSubscription':
                return $this->handleCreateOrderForSubscription();
            case 'pauseSubscription':
                return $this->handlePauseSubscription();
            case 'resumeSubscription':
                return $this->handleResumeSubscription();
            case 'loadPossibleStatesAndReasons':
                return $this->handleLoadPossibleStatesAndReasons();
            case 'performSelfCheck':
                return $this->handlePerformSelfCheck();
            default;
                throw new Exception('Unrecognized action "' . Text::filterXSS($action) . '"');
        }
    }


    /**
     * Checks the access to the merchant seller central for the given data.
     *
     * Note that we expect an ErrorResponse with the Error-Code InvalidOrderReferenceId.
     *
     *  data: {
     * 'merchantId': merchantId,
     * 'accessKey': accessKey,
     * 'secretKey': secretKey,
     * 'environment': environment,
     * 'region': region,
     * 'clientId': clientId
     * }
     *
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    private function handleCheckAccess() {
        $result = [
            'result' => self::RESULT_UNKNOWN,
            'messages' => [],
            'data' => []
        ];


        // check configuration first
        $privateKey = $this->config->getPrivateKey();
        $publicKeyId = $this->config->getPublicKeyId();
        if(empty($privateKey)) {
            $result['result'] = self::RESULT_FAIL;
            $result['messages'][] = 'Private Key is missing.';
            return $result;
        }
        if(empty($publicKeyId)) {
            $result['result'] = self::RESULT_FAIL;
            $result['messages'][] = 'Public Key ID is missing.';
            return $result;
        }

        $adapter = new ApiAdapter();
        $request = new GetChargePermission(Constants::TEST_REFERENCE_ID);
        // try request
        try {
            $testResult = $adapter->execute($request);
            if ($testResult instanceof Error) {
                /** @var Error $testResult */
                switch ($testResult->getHttpErrorCode()) {
                    case 404:
                        // Success! The api did not find the test id, which is expected
                        $result['result'] = self::RESULT_SUCCESS;
                        break;
                    case 403:
                        if($testResult->getReasonCode() === Error::REASON_CODE_INVALID_ACCOUNT_STATUS) {
                            // the account is not allowed to do this (it might be suspended or not yet completely configured)
                            $result['result'] = self::RESULT_FAIL;
                            $result['messages'][] = 'Account Status is invalid';
                        } else {
                            // the access key is wrong
                            $result['result'] = self::RESULT_FAIL;
                            $result['messages'][] = 'Private Key or Key ID is wrong';
                        }
                        break;
                    default:
                        $result['result'] = self::RESULT_FAIL;
                        $result['messages'][] = 'Unexpected Error Code ' . $testResult->getReasonCode();
                        break;
                }
            } else {
                $result['result'] = self::RESULT_ERROR;
                $result['messages'][] = 'Unexpected / no reply from Amazon Pay servers.';
            }
        } catch (Exception $e) {
            $result['result'] = self::RESULT_ERROR;
            $result['messages'][] = 'Exception: ' . $e->getMessage();
        }
        return $result;
    }

    private function handleCreateKey(): array {
        $result = [
            'messages' => []
        ];
        if(!Crypto::getInstance()->createKeyPair()) {
            $result['result'] = self::RESULT_ERROR;
            $result['messages'][] = 'Creation of key failed - incomplete key data created.';
        } else {
            $result['result'] = self::RESULT_SUCCESS;
        }
        $result['data'] = ['publickey' => $this->config->getPublicKey()];
        return $result;
    }

    /**
     * Searches for orders.
     * @return array
     */
    private function handleSearchOrders(): array {
        $searchValue = $this->request['searchValue'];
        $orders = $this->database->searchChargePermissions($searchValue);
        foreach ($orders as &$order) {
            Shop::Smarty()->assign('lpaOrder', $order);
            $order->html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/snippets/order_item.tpl');
        }

        $result = [
            'result' => self::RESULT_SUCCESS,
            'orders' => $orders
        ];
        return $result;
    }

    /**
     * Loads order paginated.
     * @return array
     * @throws \SmartyException
     */
    private function handleLoadOrders(): array {
        $offset = (int)$this->request['offset'];
        $limit = (int)$this->request['limit'];
        $sortBy = $this->request['sortBy'] ?? Database::ORDER_DEFAULT_SORTING;
        $sortDirection = $this->request['sortDirection']  ?? Database::ORDER_DEFAULT_SORTING_DIRECTION;
        $statusFilters = $this->request['statusFilters'] ?? [];
        $statusReasonFilters = $this->request['statusReasonFilters'] ?? [];

        $statusFilters = array_filter($statusFilters);
        $statusReasonFilters = array_filter($statusReasonFilters);

        $orders = $this->database->loadChargePermissions($offset, $limit, $sortBy, $sortDirection, $statusFilters, $statusReasonFilters);
        foreach ($orders as &$order) {
            Shop::Smarty()->assign('lpaOrder', $order);
            $order->html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/snippets/order_item.tpl');
        }

        $result = [
            'result' => self::RESULT_SUCCESS,
            'orders' => $orders
        ];
        return $result;
    }

    /**
     * Refreshes an order reference and attached objects from amazon pay.
     * @return array
     */
    private function handleRefreshChargePermission(): array {
        $chargePermissionId = $this->request['chargePermissionId'];
        if (empty($chargePermissionId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargePermissionId'];
        }
        try {
            $statusController = new StatusController();
            $statusController->performRefreshForChargePermission($chargePermissionId);
            return ['result' => self::RESULT_SUCCESS];
        } catch (Exception $e) {
            return ['result' => self::RESULT_ERROR, 'message' => $e->getCode() . ' - ' . $e->getMessage()];
        }
    }

    /**
     * Gets the data for an order reference from the database.
     * @return array
     * @throws \SmartyException
     */
    private function handleGetChargePermission(): array {
        $chargePermissionId = $this->request['chargePermissionId'];
        if (empty($chargePermissionId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargePermissionId'];
        }
        $chargePermission = $this->database->loadChargePermission($chargePermissionId, true);
        if (null === $chargePermission) {
            return ['result' => self::RESULT_ERROR, 'message' => 'ChargePermission not found in database'];
        }
        $chargePermission->charges = $this->database->loadChargesForChargePermission($chargePermissionId, true);
        foreach ($chargePermission->charges as &$charge) {
            // If your IDE shows a warning that chargeId is protected, it's wrong - $charge here is an stdClass, not a Charge object.
            /** @var \stdClass $charge */
            $charge->refunds = $this->database->loadRefundsForCharge($charge->chargeId, true);
            /** @var \stdClass $charge */
            if(!empty($charge->shopOrderId)) {
                /** @var \stdClass $charge */
                $charge->order = $this->database->getShopOrder((int) $charge->shopOrderId);
            } else {
                $charge->order = null;
            }
        }
        Shop::Smarty()->assign('lpaOrder', $chargePermission);
        $chargePermission->html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/snippets/order_detail.tpl');
        return ['result' => self::RESULT_SUCCESS, 'order' => $chargePermission];
    }

    private function handleCloseChargePermission() {
        $chargePermissionId = $this->request['chargePermissionId'];
        $cancelPendingCharges = (isset($this->request['cancelPendingCharges']) && $this->request['cancelPendingCharges'] === 'on');
        $closureReason = empty($this->request['closureReason']) ? 'undefined' : $this->request['closureReason'];
        if (empty($chargePermissionId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargePermissionId'];
        }
        $adapter = new ApiAdapter();
        $chargePermission = $adapter->execute(new CloseChargePermission($chargePermissionId, $closureReason, $cancelPendingCharges));
        if ($chargePermission instanceof Error) {
            return ['result' => self::RESULT_ERROR, 'message' => $chargePermission->getHttpErrorCode() . ': ' . $chargePermission->getReasonCode()];
        }
        $statusController = new StatusController();
        $statusController->performRefreshForChargePermission($chargePermissionId, true);
        return ['result' => self::RESULT_SUCCESS];
    }

    private function handleCreateCharge() {
        $chargePermissionId = $this->request['chargePermissionId'];
        $chargeAmountAmount = $this->request['chargeAmountAmount'];
        $chargeAmountCurrencyCode = $this->request['chargeAmountCurrencyCode'];
        if (empty($chargePermissionId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargePermissionId'];
        }
        if (empty($chargeAmountAmount)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargeAmountAmount'];
        }
        if (empty($chargeAmountCurrencyCode)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargeAmountCurrencyCode'];
        }
        $adapter = new ApiAdapter();
        $chargeAmount = new Price(['amount' => $chargeAmountAmount, 'currencyCode' => $chargeAmountCurrencyCode]);
        $charge = $adapter->execute(new CreateCharge($chargePermissionId, $chargeAmount, $this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE, $this->config->getAuthorizationMode() === Config::AUTHORIZATION_MODE_OMNI));
        if($charge instanceof Error) {
            return ['result' => self::RESULT_ERROR, 'message' => $charge->getHttpErrorCode() . ': ' . $charge->getReasonCode()];
        }
        /** @var Charge $charge */
        $charge->setChargePermissionId($chargePermissionId);
        if(isset($this->request['shopOrderId'])) {
            $charge->setShopOrderId((int) $this->request['shopOrderId']);
        }
        $statusController = new StatusController();
        $statusController->handleCharge($charge, true); // the status controller will save the new charge
        return ['result' => self::RESULT_SUCCESS];
    }

    private function handleCaptureCharge() {
        $chargeId = $this->request['chargeId'];
        $captureAmountAmount = $this->request['captureAmountAmount'];
        $captureAmountCurrencyCode = $this->request['captureAmountCurrencyCode'];
        if (empty($chargeId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargeId'];
        }
        if (empty($captureAmountAmount)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter captureAmountAmount'];
        }
        if (empty($captureAmountCurrencyCode)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter captureAmountCurrencyCode'];
        }
        $adapter = new ApiAdapter();
        $captureAmount = new Price(['amount' => $captureAmountAmount, 'currencyCode' => $captureAmountCurrencyCode]);
        $charge = $adapter->execute(new CaptureCharge($chargeId, $captureAmount));
        if($charge instanceof Error) {
            return ['result' => self::RESULT_ERROR, 'message' => $charge->getHttpErrorCode() . ': ' . $charge->getReasonCode()];
        }
        /** @var Charge $charge */
        $statusController = new StatusController();
        $statusController->handleCharge($charge);
        return ['result' => self::RESULT_SUCCESS];
    }

    private function handleCancelCharge() {
        $chargeId = $this->request['chargeId'];
        $cancellationReason = empty($this->request['cancellationReason']) ? 'undefined' : $this->request['cancellationReason'];
        if (empty($chargeId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargeId'];
        }
        if (empty($cancellationReason)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter cancellationReason'];
        }
        $adapter = new ApiAdapter();
        $charge = $adapter->execute(new CancelCharge($chargeId, $cancellationReason));
        if($charge instanceof Error) {
            return ['result' => self::RESULT_ERROR, 'message' => $charge->getHttpErrorCode() . ': ' . $charge->getReasonCode()];
        }
        /** @var Charge $charge */
        $statusController = new StatusController();
        $statusController->handleCharge($charge);
        return ['result' => self::RESULT_SUCCESS];
    }

    private function handleCreateRefund() {
        $chargeId = $this->request['chargeId'];
        $refundAmountAmount = $this->request['refundAmountAmount'];
        $refundAmountCurrencyCode = $this->request['refundAmountCurrencyCode'];
        if (empty($chargeId)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter chargeId'];
        }
        if (empty($refundAmountAmount)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter refundAmountAmount'];
        }
        if (empty($refundAmountCurrencyCode)) {
            return ['result' => self::RESULT_ERROR, 'message' => 'Missing parameter refundAmountCurrencyCode'];
        }
        $adapter = new ApiAdapter();
        $refundAmount = new Price(['amount' => $refundAmountAmount, 'currencyCode' => $refundAmountCurrencyCode]);
        $refund = $adapter->execute(new CreateRefund($chargeId, $refundAmount));
        if($refund instanceof Error) {
            return ['result' => self::RESULT_ERROR, 'message' => $refund->getHttpErrorCode() . ': ' . $refund->getReasonCode()];
        }
        /** @var Refund $refund */
        $refund->setChargeId($chargeId);
        $statusController = new StatusController();
        $statusController->handleRefund($refund, true); // the status controller will save the new refund
        return ['result' => self::RESULT_SUCCESS];
    }
    private function handleLoadPossibleStatesAndReasons() {
        $chargePermissionStates = $this->database->getExistingChargePermissionStates();
        $chargePermissionStateReasons = $this->database->getExistingChargePermissionStateReasons();
        return ['result' => self::RESULT_SUCCESS, 'chargePermissionStates' => $chargePermissionStates, 'chargePermissionStateReasons' => $chargePermissionStateReasons];
    }

    private function handleLoadSubscriptions(): array {
        $offset = (int)$this->request['offset'];
        $limit = (int)$this->request['limit'];

        $subscriptions = $this->database->loadSubscriptions($offset, $limit);
        foreach ($subscriptions as &$subscription) {
            Shop::Smarty()->assign('lpaSubscription', $subscription);
            $subscription->html = Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/snippets/subscription_item.tpl');
        }
        $result = [
            'result' => self::RESULT_SUCCESS,
            'subscriptions' => $subscriptions
        ];
        return $result;
    }

    private function handleCancelSubscription(): array {
        $subscriptionId = (int) $this->request['subscriptionId'];
        $subscriptionController = new SubscriptionController($this->plugin);
        try {
            $subscriptionController->cancelSubscription($subscriptionId, Subscription::REASON_MERCHANT_CANCELED);
        } catch (Exception $e) {
            return [
                'result' => self::RESULT_ERROR,
                'message' => $e->getMessage()
            ];
        }
        return [
            'result' => self::RESULT_SUCCESS
        ];
    }

    private function handleCreateOrderForSubscription(): array {
        $subscriptionId = (int) $this->request['subscriptionId'];
        $subscriptionController = new SubscriptionController($this->plugin);
        try {
            $newOrder = $subscriptionController->createOrderForSubscription($subscriptionId);
            $subscriptionController->renewSubscription($subscriptionId, true); // renew subscription for NOW + Interval
        } catch(Exception $e) {
            return [
                'result' => self::RESULT_ERROR,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
        return [
            'result' => self::RESULT_SUCCESS,
            'newOrderNumber' => $newOrder->cBestellNr
        ];
    }

    private function handlePauseSubscription(): array {
        $subscriptionId = (int) $this->request['subscriptionId'];
        $subscriptionController = new SubscriptionController($this->plugin);
        try {
           $subscriptionController->setSubscriptionToReview($subscriptionId, Subscription::REASON_MERCHANT_PAUSED);
        } catch(Exception $e) {
            return [
                'result' => self::RESULT_ERROR,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
        return [
            'result' => self::RESULT_SUCCESS
        ];
    }

    private function handleResumeSubscription(): array {
        $subscriptionId = (int) $this->request['subscriptionId'];
        $createNewOrderNow = $this->request['createNewOrderNow'] === 'Y';
        $subscriptionController = new SubscriptionController($this->plugin);
        $newOrder = null;
        try {
           $subscriptionController->setSubscriptionToActive($subscriptionId);
           if($createNewOrderNow) {
               $newOrder = $subscriptionController->createOrderForSubscription($subscriptionId);
               $subscriptionController->renewSubscription($subscriptionId, true);
           }
        } catch(Exception $e) {
            return [
                'result' => self::RESULT_ERROR,
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
        return [
            'result' => self::RESULT_SUCCESS,
            'newOrderNumber' =>  $createNewOrderNow ? $newOrder->cBestellNr : ''
        ];
    }

    private function handlePerformSelfCheck(): array {
        return [
            'result' => self::RESULT_SUCCESS,
            'html' => $this->performSelfCheck()
        ];
    }
}
