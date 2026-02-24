<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\DeliveryDetail;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\DeliveryTrackersPayload;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CaptureCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CloseChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\DeliveryTrackers;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCharge;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class SyncController
 *
 * Handles everything related to syncing orders between ERP and shop.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class SyncController {

    use JtlLoggerTrait;

    private $config;
    private $database;
    private $plugin;
    private $adapter;
    private $statusController;

    public function __construct(PluginInterface $plugin) {
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->plugin = $plugin;
        $this->adapter = new ApiAdapter();
        $this->statusController = new StatusController();

    }

    /**
     *
     * Handles the update of a single order.
     *
     * @param \stdClass $orderBefore
     * @param Bestellung $orderAfter
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\StatusHandlerException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     * @throws \Exception
     */
    public function handleOrderUpdate(\stdClass $orderBefore, Bestellung $orderAfter): void {
        if ((int)$orderBefore->cStatus === (int)$orderAfter->cStatus) {
            // Ignore unchanged orders.
            return;
        }
        $chargePermission = $this->database->loadChargePermissionByJtlOrderId((int)$orderBefore->kBestellung);
        if ($chargePermission !== null) {
            // only handle orders that we recognize and know about
            $this->debugLog('Started handling order ' . $orderAfter->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId(), __CLASS__);
            $this->handleCapture($orderAfter, $chargePermission);
            $this->handleDeliveryNotification($orderAfter, $chargePermission);
            $this->debugLog('Finished handling order ' . $orderAfter->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId(), __CLASS__);
            return;
        }

        /*
         * This order might be a subscription recurring order in which case the charge permission can be found via the initialShopOrderId
         */
        $chargePermission = $this->database->loadChargePermissionForSubscriptionOrderId((int)$orderBefore->kBestellung);
        if($chargePermission !== null) {
            // this is indeed a recurring subscription order
            $this->debugLog('Started handling recurring order ' . $orderAfter->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId(), __CLASS__);
            $this->handleCapture($orderAfter, $chargePermission, true);
            $this->handleDeliveryNotification($orderAfter, $chargePermission);
            $this->debugLog('Finished handling recurring order ' . $orderAfter->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId(), __CLASS__);
            return;
        }
        if ($orderAfter->cZahlungsartName === Constants::PAYMENT_METHOD_NAME) {
            // This might be a problem, we log this
            $this->errorLog('Warning: Order ' . $orderAfter->cBestellNr . ' has payment method ' . Constants::PAYMENT_METHOD_NAME . ' but is unknown to the plugin! Order will be ignored. Please handle manually, if necessary.', __CLASS__);
        }
    }

    /**
     * This function specifically handles orders that *should be captured* but are not.
     * This is a fallback for when handleOrderUpdate does not trigger or execute properly during the actual sync.
     *
     * This is done by identifying orders that were shipped or partially shipped and where there is an authorized charge for that order that has not been captured.
     *
     * Additionally, only charges that are at max 30 days old are considered to prevent an endlessly increasing list of charges to handle.
     *
     * @return void
     */
    public function handleDesyncedOrders(): void {
        // pre-emptively check capture mode to improve performance of this.
        $captureMode = $this->config->getCaptureMode();
        if ($captureMode === Config::CAPTURE_MODE_MANUAL || $captureMode === Config::CAPTURE_MODE_IMMEDIATE) {
            // do nothing, we are in manual or immediate capture mode so this would never do anything.
            return;
        }
        // Get all charges that are authorized but not captured
        $authorizedCharges = $this->database->getChargesByState([StatusDetails::STATUS_AUTHORIZED], true);
        if (empty($authorizedCharges)) {
            // No authorized charges found
            return;
        }
        $now = time();
        $cutoff = $now - 2592000; // right now, minus 30 days
        // reduce charges to check to only those that are not older than 30 days and not expired
        $authorizedCharges = array_filter($authorizedCharges, static function ($charge) use ($now, $cutoff) {
            $creationTimestamp = strtotime($charge->creationTimestamp);
            $expirationTimestamp = strtotime($charge->expirationTimestamp);
            if($creationTimestamp === false || $expirationTimestamp === false) {
                return false;
            }
            return !($creationTimestamp < $cutoff || $expirationTimestamp < $now);
        });
        foreach ($authorizedCharges as $authorizedCharge) {
            $chargePermission = $this->database->loadChargePermission($authorizedCharge->chargePermissionId, true);
            /** @noinspection MissingIssetImplementationInspection */
            if (empty($chargePermission) || empty($chargePermission->shopOrderId) || $chargePermission->status === StatusDetails::STATUS_CLOSED || $chargePermission->status === StatusDetails::STATUS_CANCELED) {
                // Don't handle charge permissions that have no order id or are closed or canceled
                continue;
            }
            $order = null;
            $shopOrderId = (int) $chargePermission->shopOrderId;
            $chargePermission = $this->database->loadChargePermission($authorizedCharge->chargePermissionId); // load actual charge permission object for further function calls, note that this does not include the order ID!
            if ($captureMode === Config::CAPTURE_MODE_ON_SHIPPING_PARTIAL) {
                $order = Shop::Container()->getDB()->executeQueryPrepared('SELECT kBestellung, cStatus, cBestellNr FROM tbestellung WHERE kBestellung = :kBestellung AND (cStatus = :statusPartiallyShipped OR cStatus = :statusCompletelyShipped)',
                    [
                        'kBestellung' => $shopOrderId,
                        'statusPartiallyShipped' => (string)BESTELLUNG_STATUS_TEILVERSANDT,
                        'statusCompletelyShipped' => (string)BESTELLUNG_STATUS_VERSANDT
                    ],
                    ReturnType::SINGLE_OBJECT
                );
            } else {
                $order = Shop::Container()->getDB()->executeQueryPrepared('SELECT kBestellung, cStatus, cBestellNr FROM tbestellung WHERE kBestellung = :kBestellung AND cStatus = :statusCompletelyShipped',
                    [
                        'kBestellung' => $shopOrderId,
                        'statusCompletelyShipped' => (string)BESTELLUNG_STATUS_VERSANDT
                    ],
                    ReturnType::SINGLE_OBJECT
                );
            }
            if (!empty($order)) {
                // This order should have been captured but was not!
                $this->debugLog('Identified desynced order ' . $order->cBestellNr . '. Trying to capture it now.');
                $orderObject = new Bestellung((int)$order->kBestellung);
                try {
                    $this->captureOnOrder($orderObject, $chargePermission);
                } catch (\Exception $e) {
                    $this->debugLog('Failed with exception while trying to capture on desynced order: ' . $e->getMessage());
                }
            }
        }
    }

    private function handleCapture(Bestellung $order, ChargePermission $chargePermission, $isRecurringOrder = false): void {
        // check capture mode
        $captureMode = $this->config->getCaptureMode();
        if ($captureMode === Config::CAPTURE_MODE_MANUAL) {
            // do nothing, we are in manual capture mode
            return;
        }
        if ($captureMode === Config::CAPTURE_MODE_IMMEDIATE) {
            if (empty($order->dBezahltDatum)) {
                $this->errorLog('Warning: Order ' . $order->cBestellNr . ' has no dBezahltDatum although we are in immediate capture mode! Order *might* not have been captured. Please check order and handle manually, if necessary.', __CLASS__);
            }
            return;
        }
        // Actual capturing happens here.
        if ($captureMode === Config::CAPTURE_MODE_ON_SHIPPING_PARTIAL) {
            if ((int)$order->cStatus === BESTELLUNG_STATUS_TEILVERSANDT || (int)$order->cStatus === BESTELLUNG_STATUS_VERSANDT) {
                // Order is in a valid state to be captured
                $this->captureOnOrder($order, $chargePermission, $isRecurringOrder);
            } else {
                $this->debugLog('Order new status ' . $order->cStatus . ' for order ' . $order->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId() . ' is not relevant for capture.', __CLASS__);
            }
        } elseif ($captureMode === Config::CAPTURE_MODE_ON_SHIPPING_COMPLETE) {
            if ((int)$order->cStatus === BESTELLUNG_STATUS_VERSANDT) {
                // order is in  a valid state to be captured
                $this->captureOnOrder($order, $chargePermission, $isRecurringOrder);
            } else {
                $this->debugLog('Order new status ' . $order->cStatus . ' for order ' . $order->cBestellNr . ' with ChargePermissionId ' . $chargePermission->getChargePermissionId() . ' is not relevant for capture.', __CLASS__);
            }
        }
    }

    private function handleDeliveryNotification(Bestellung $order, ChargePermission $chargePermission): void {
        // check delivery tracker information
        if ((int)$order->cStatus === BESTELLUNG_STATUS_VERSANDT && $this->config->isDeliveryNotificationsEnabled()) {
            // the order is now completely delivered, send out delivery notifications
            $this->sendDeliveryNotificationForOrder($order, $chargePermission);
        }
    }

    /**
     * Performs a capture on an order.
     * But first, checks some pre-requisites, like:
     * - Has the order a paid date? If so, we do nothing.
     * - Is the order paid completely? If so, we do nothing.
     * - Is there maybe already a capture running on the order, which is pending? If so, we do nothing.
     * - Is there at least one capture that was fully completed? If so, we do nothing.
     *
     * @param Bestellung $order
     * @param ChargePermission $chargePermission
     * @param bool $isRecurringOrder - flag if the order is recurring
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\StatusHandlerException
     */
    private function captureOnOrder(Bestellung $order, ChargePermission $chargePermission, $isRecurringOrder = false): void {
        $chargePermissionId = $chargePermission->getChargePermissionId();
        if (!empty($order->dBezahltDatum) && $order->dBezahltDatum !== '0000-00-00') {
            $this->debugLog('Order ' . $order->cBestellNr . ' with AmazonOrderReferenceId ' . $chargePermissionId . ' - capture prevented because order already has a dBezahltDatum "' . $order->dBezahltDatum . '".', __CLASS__);
            return;
        }
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if ($paymentMethodModule->isOrderPaidCompletely($order)) {
            $this->debugLog('Order ' . $order->cBestellNr . ' with AmazonOrderReferenceId ' . $chargePermissionId . ' - capture prevented because order is already paid completely according to incoming payments.', __CLASS__);
            return;
        }
        // get all charge objects and check if we have at least one that is in state authorized to be captured upon or if we already have pending captures against this order
        if(!$isRecurringOrder) {
            // for "normal" orders, get all charge permission related charges
            $charges = $this->database->loadChargesForChargePermission($chargePermissionId);
        } else {
            // we have to only load charges for this specific order as other charges on this charge permission may belong to other/older/newer orders!
            $charges = $this->database->loadChargesByJtlOrderId((int) $order->kBestellung);
        }
        $capturableCharge = null;
        foreach ($charges as $charge) {
            /** @var Charge $charge */
            if ($charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZATION_INITIATED) {
                // our info on this charge may be outdated, we have to refresh it because it may now be AUTHORIZED
                $refreshedCharge = $this->adapter->execute(new GetCharge($charge->getChargeId()));
                if ($refreshedCharge instanceof Error) {
                    $this->errorLog('Warning: Order ' . $order->cBestellNr . ' with ChargePermissionId ' . $chargePermissionId . ': Failed to refresh charge in pending state: ' . $refreshedCharge->getReasonCode(), __CLASS__);
                } else {
                    /** @var Charge $refreshedCharge */
                    $this->statusController->handleCharge($refreshedCharge);
                    if ($refreshedCharge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZED) {
                        // the refresh was successful and yielded a capturable charge. break from the loop
                        $capturableCharge = $refreshedCharge;
                        break;
                    }
                }
            } elseif ($charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZED) {
                // the charge could be captured against, break from the loop
                $capturableCharge = $charge;
                break;
            }
        }
        // we arrived here, that means that there is not capturable charge for this order
        if (null === $capturableCharge) {
            // ...we did not find a charge that we could capture on now.
            $this->errorLog('Warning: Order ' . $order->cBestellNr . ' with ChargePermissionId ' . $chargePermissionId . ' should be captured now but it has NO open charges! Please handle manually, if necessary.', __CLASS__);
            return;
        }
        // All is good, try to capture on the capturable charge that we found, and let the status handler handle the result.
        $updatedCharge = $this->adapter->execute(new CaptureCharge($capturableCharge->getChargeId(), $capturableCharge->getChargeAmount()));
        if ($updatedCharge instanceof Error) {
            $this->errorLog('Warning: Order ' . $order->cBestellNr . ' with ChargePermissionId ' . $chargePermissionId . ' should be captured now but it has failed with Error: ' . $updatedCharge->getReasonCode(), __CLASS__);
        } else {
            /** @var Charge $updatedCharge */
            $this->statusController->handleCharge($updatedCharge);
        }
    }

    /**
     * This method is called by the payment method module if an order gets canceled, therefore we need not check if the given order is an Amazon Pay order in the first place.
     * We might try to close the charge permission if it is not already in a closed state
     *
     * NOTE: WE DO NOT AUTO-REFUND EVERYTHING! This is a manual step to be done by the merchant!
     *
     * @param int $jtlShopOrderId
     * @throws \Exception
     */
    public function handleOrderCanceled(int $jtlShopOrderId): void {
        // Note: we do not check for subscription orders here, only for initial orders, so cancelling a recurring subscription order (not the initial order!) does NOT automatically close the charge permission because other / followup orders may still be active
        $chargePermission = $this->database->loadChargePermissionByJtlOrderId($jtlShopOrderId);
        if (null !== $chargePermission) {
            if ($chargePermission->getStatusDetails()->getState() !== StatusDetails::STATUS_CLOSED) {
                $updatedChargePermission = $this->adapter->execute(new CloseChargePermission($chargePermission->getChargePermissionId(), Translation::getInstance()->get(Translation::KEY_CLOSURE_REASON_STORNO), true));
                if ($updatedChargePermission instanceof Error) {
                    $this->errorLog('ChargePermission with id ' . $chargePermission->getChargePermissionId() . ' should be closed now but it has failed with Error: ' . $updatedChargePermission->getReasonCode(), __CLASS__);
                } else {
                    /** @var ChargePermission $updatedChargePermission */
                    $statusController = new StatusController();
                    $statusController->handleChargePermission($updatedChargePermission);
                }
            }
            // else we silently ignore this.
        }
    }

    private function sendDeliveryNotificationForOrder(Bestellung $order, ChargePermission $chargePermission): void {
        try {
            $chargePermissionId = $chargePermission->getChargePermissionId();
            $this->debugLog('Trying to send delivery notifications for with ChargePermissionId ' . $chargePermissionId, __CLASS__);
            if (empty($chargePermissionId)) {
                return;
            }
            // first, we need to identify the corresponding deliveries (these are entries from tversand)
            $deliveries = $this->database->getDeliveriesForOrder($order->kBestellung);
            $deliveryDetails = [];
            $this->debugLog('Found deliveries: ' . print_r($deliveries, true), __CLASS__);
            foreach ($deliveries as $delivery) {
                if (isset($delivery->cLogistik) && $delivery->cLogistik !== '') {
                    if (isset($delivery->cIdentCode) && $delivery->cIdentCode !== '') {
                        $carrierCode = DeliveryDetail::mapToCarrierCode($delivery->cLogistik);
                        if ($carrierCode !== null) {
                            $this->debugLog('Mapped carrier code "' . $delivery->cLogistik . '" to "' . $carrierCode . '"', __CLASS__);
                            $deliveryDetails[] = new DeliveryDetail([
                                'trackingNumber' => $delivery->cIdentCode,
                                'carrierCode' => $carrierCode
                            ]);
                        } else {
                            $this->debugLog('Carrier code "' . $delivery->cLogistik . '" could not be mapped. Skipping notification.', __CLASS__);
                        }
                    } else {
                        $this->debugLog('Tracking code (cIdentCode) is not set. Skipping notification.', __CLASS__);
                    }
                } else {
                    $this->debugLog('Delivery method (cLogistik) is not set. Skipping notification.', __CLASS__);
                }
            }
            if (!empty($deliveryDetails)) {
                $deliveryTrackersPayload = new DeliveryTrackersPayload([
                    'chargePermissionId' => $chargePermissionId,
                ]);
                $deliveryTrackersPayload->setDeliveryDetails($deliveryDetails);
                $response = $this->adapter->execute(new DeliveryTrackers($deliveryTrackersPayload));
                if ($response instanceof Error) {
                    throw new \Exception($response->getReasonCode() . ' - ' . $response->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->debugLog('Failed to send delivery notification. Continuing with normal operation. Exception message: ' . $e->getMessage(), __CLASS__);
        }
    }
}