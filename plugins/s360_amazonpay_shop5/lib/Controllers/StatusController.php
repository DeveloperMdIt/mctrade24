<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CaptureCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetRefund;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\StatusHandlerException;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class StatusController
 *
 * This controller handles status changes of objects.
 * It basically handles the logic of status changes and reason codes for the Amazon Pay types.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class StatusController {


    use JtlLoggerTrait;

    private $config;
    private $database;
    private $adapter;
    private $plugin;

    public function __construct() {
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->adapter = new ApiAdapter();
        $this->plugin = Plugin::getInstance();
    }

    public function handleChargePermission(ChargePermission $chargePermission) {
        /** @var ChargePermission $chargePermissionBefore */
        $chargePermissionBefore = $this->database->loadChargePermission($chargePermission->getChargePermissionId());
        $order = $this->database->loadJtlOrderForChargePermissionId($chargePermission->getChargePermissionId());
        $cancelSubscription = false;

        if (null === $chargePermissionBefore || null === $order) {
            $this->debugLog('No existing order or charge permission found for charge permission id:' . $chargePermission->getChargePermissionId(), __CLASS__);
            // Unknown charge permission - do not handle this.
            throw new StatusHandlerException($chargePermission->getChargePermissionId(), StatusHandlerException::CODE_UNKNOWN_OBJECT);
        }

        switch ($chargePermission->getStatusDetails()->getState()) {
            case StatusDetails::STATUS_CHARGEABLE:
                if ($chargePermissionBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_CHARGEABLE) {
                    // the charge permission is now chargeable. This does not really imply anything for us without context. Take note and update the object.
                    $this->debugLog('Charge permission with id: ' . $chargePermission->getChargePermissionId() . ' has switched to state CHARGEABLE.', __CLASS__);
                }
                break;
            case StatusDetails::STATUS_NON_CHARGEABLE:
                if ($chargePermissionBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_NON_CHARGEABLE) {
                    // the charge permission is now non-chargeable. This does not really imply anything for us without context. Take note and update the object.
                    $this->debugLog('Charge permission with id: ' . $chargePermission->getChargePermissionId() . ' has switched to state NON-CHARGEABLE because of reasons: ' . print_r($chargePermission->getStatusDetails()->getReasons(), true), __CLASS__);
                }
                break;
            case StatusDetails::STATUS_CLOSED:
                if ($chargePermissionBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_CLOSED) {
                    // the charge permission is now closed. Take note and update the object.
                    $this->debugLog('Charge permission with id: ' . $chargePermission->getChargePermissionId() . ' has switched to state CLOSED because of reasons: ' . print_r($chargePermission->getStatusDetails()->getReasons(), true), __CLASS__);
                    $cancelSubscription = true;
                }
                break;
            default:
                throw new StatusHandlerException($chargePermission->getStatusDetails()->getState(), StatusHandlerException::CODE_UNEXPECTED_STATUS);
        }
        // Before we save the charge permission, we might need to fix it, if it does not contain an amount limit (this may happen if we refresh an old charge permission, created with API CV2-1 instead of CV2-2)
        if($chargePermission->getLimits() === null || $chargePermission->getLimits()->getAmountLimit() === null || $chargePermission->getLimits()->getAmountLimit()->getAmount() === null) {
            $chargePermission->setLimits($chargePermissionBefore->getLimits());
        }
        $this->database->saveChargePermission($chargePermission, $order);
        if($cancelSubscription) {
            // If the charge permission is related to a submission and it got closed, we should also cancel the subscription
            $subscriptionController = new SubscriptionController($this->plugin);
            $subscriptionController->cancelSubscriptionForChargePermission($chargePermission->getChargePermissionId(), Subscription::REASON_CHARGE_PERMISSION_CLOSED);
        }
    }

    public function handleCharge(Charge $charge, bool $isNewCharge = false): void {
        /** @var Charge $chargeBefore */
        $chargeBefore = $this->database->loadCharge($charge->getChargeId());
        if (null === $chargeBefore && !$isNewCharge) {
            $this->debugLog('No existing charge found for charge id:' . $charge->getChargeId(), __CLASS__);
            // Unknown charge - do not handle this.
            throw new StatusHandlerException($charge->getChargeId(), StatusHandlerException::CODE_UNKNOWN_OBJECT);
        }
        if (null === $chargeBefore && $isNewCharge) {
            // fake the chargeBefore to be a copy of the charge we got, but set the copy to the initial state so we handle it correctly in the following steps.
            $chargeBefore = (new Charge())->fillFromDatabaseObject($charge->getDatabaseObject());
            $statusDetails = new StatusDetails();
            $statusDetails->setState(StatusDetails::STATUS_AUTHORIZATION_INITIATED);
            $chargeBefore->setStatusDetails($statusDetails);
        }

        switch ($charge->getStatusDetails()->getState()) {
            case StatusDetails::STATUS_AUTHORIZATION_INITIATED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_AUTHORIZATION_INITIATED) {
                    // this should not happen as this would mean the charge returned to its initial state
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state AUTHORIZATION_INITIATED which should NOT be possible.', __CLASS__);
                    throw new StatusHandlerException($charge->getStatusDetails()->getState(), StatusHandlerException::CODE_UNEXPECTED_STATUS);
                }
                break;
            case StatusDetails::STATUS_AUTHORIZED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_AUTHORIZED) {
                    // the charge is now authorized. This means that a pending order can now be released, also we need to capture the charge now if we are in IMMEDIATE capture mode.
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state AUTHORIZED which means that a pending status is now resolved. Releasing order for collection by the ERP.', __CLASS__);
                    $this->database->releaseOrderForErp($chargeBefore->getChargePermissionId());
                    if($this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE) {
                        $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state AUTHORIZED and we are in immediate capture mode which means we also capture it now.', __CLASS__);
                        $captureChargeRequest = new CaptureCharge($chargeBefore->getChargeId(), $chargeBefore->getChargeAmount());
                        $updatedCharge = $this->adapter->execute($captureChargeRequest);
                        if($updatedCharge instanceof Error) {
                            $this->handleMerchantNotification($chargeBefore->getChargePermissionId(), Translation::getInstance()->get(Translation::KEY_MERCHANT_INFO_FAILED_CAPTURE));
                            break;
                        }
                        $charge->setShopOrderId($chargeBefore->getShopOrderId());
                        $charge->setChargePermissionId($chargeBefore->getChargePermissionId());
                        $this->database->saveCharge($charge);
                        $this->handleCharge($charge);
                        return;
                    }
                }
                break;
            case StatusDetails::STATUS_DECLINED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_DECLINED) {
                    // the charge is not authorized.
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state DECLINED with reason code: ' . $charge->getStatusDetails()->getReasonCode(), __CLASS__);
                    // further action depends on the previous state and the reason code for the decline
                    switch ($charge->getStatusDetails()->getReasonCode()) {
                        case StatusDetails::REASON_CODE_AMAZON_REJECTED:
                        case StatusDetails::REASON_CODE_HARD_DECLINED:
                        case StatusDetails::REASON_CODE_TRANSACTION_TIMED_OUT:
                            // the payment was hard declined or rejected or timed out. Handle this as hard decline.
                            $this->handleHardDecline($chargeBefore->getChargePermissionId());
                            $this->handleMerchantNotification($chargeBefore->getChargePermissionId(), Translation::getInstance()->get(Translation::KEY_MERCHANT_INFO_AMAZON_CANCELED_CHARGE));
                            break;
                        case StatusDetails::REASON_CODE_SOFT_DECLINED:
                        case StatusDetails::REASON_CODE_PROCESSING_FAILURE:
                            if ($isNewCharge) {
                                // if this happened in a recursive call, give up.
                                $this->debugLog('Re-charge failed again with soft decline or processing failure. Giving up.', __CLASS__);
                                $this->handleHardDecline($chargeBefore->getChargePermissionId());
                            }
                            // the payment failed but may work on retry with a new charge.
                            $newCharge = $this->createNewCharge($chargeBefore);
                            if ($newCharge !== null) {
                                // we created a new charge, save the old one, then call ourselves again immediately
                                $charge->setShopOrderId($chargeBefore->getShopOrderId());
                                $charge->setChargePermissionId($chargeBefore->getChargePermissionId());
                                $this->database->saveCharge($charge);
                                /** @var Charge $newCharge */
                                $newCharge->setShopOrderId($chargeBefore->getShopOrderId());
                                $newCharge->setChargePermissionId($chargeBefore->getChargePermissionId());
                                $this->handleCharge($newCharge, true);
                                return;
                            }
                            // creating a new charge failed
                            $this->debugLog('Re-charge failed. Giving up.', __CLASS__);
                            $this->handleHardDecline($chargeBefore->getChargePermissionId());
                            break;
                        default:
                            // Should not be possible unless the API changes.
                            break;
                    }
                }
                break;
            case StatusDetails::STATUS_CANCELED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_CANCELED) {
                    // the charge is now canceled. this usually happens only on demand.
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state CANCELED with reason ' . $charge->getStatusDetails()->getReasonCode(), __CLASS__);
                    switch ($charge->getStatusDetails()->getReasonCode()) {
                        case StatusDetails::REASON_CODE_AMAZON_CANCELED:
                            // Amazon Canceled the charge - we might need to inform the merchant.
                            $this->handleMerchantNotification($chargeBefore->getChargePermissionId(), Translation::getInstance()->get(Translation::KEY_MERCHANT_INFO_AMAZON_CANCELED_CHARGE));
                            break;
                        case StatusDetails::REASON_CODE_EXPIRED_UNUSED:
                            // The Charge expired after not being used to capture within 30 days. We have to create a new charge, if possible.
                            $newCharge = $this->createNewCharge($chargeBefore);
                            if ($newCharge !== null) {
                                // we created a new charge, save the old one, then call ourselves immediately
                                $charge->setShopOrderId($chargeBefore->getShopOrderId());
                                $charge->setChargePermissionId($chargeBefore->getChargePermissionId());
                                $this->database->saveCharge($charge);
                                /** @var Charge $newCharge */
                                $newCharge->setShopOrderId($chargeBefore->getShopOrderId());
                                $newCharge->setChargePermissionId($chargeBefore->getChargePermissionId());
                                $this->handleCharge($newCharge, true);
                                return;
                            }
                            // creating a new charge failed
                            $this->debugLog('Re-charge failed. Giving up.', __CLASS__);
                            $this->handleMerchantNotification($chargeBefore->getChargePermissionId(), Translation::getInstance()->get(Translation::KEY_MERCHANT_INFO_FAILED_RECHARGE_ON_EXPIRED));
                            break;
                        case StatusDetails::REASON_CODE_MERCHANT_CANCELED:
                        case StatusDetails::REASON_CODE_CHARGE_PERMISSION_CANCELED:
                        case StatusDetails::REASON_CODE_BUYER_CANCELED:
                            // these cases are of no interest to us or we know about it already.
                            // Note that BUYER_CANCELED cannot actually happen here, this is an internal state that only happens if the buyer does not complete the checkout session
                            break;
                    }
                }
                break;
            case StatusDetails::STATUS_CAPTURE_INITIATED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_CAPTURE_INITIATED) {
                    // alright, a capture was initiated. this does not require actions on our part (we are probably the reason this happened).
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state CAPTURE_INITIATED', __CLASS__);
                }
                break;
            case StatusDetails::STATUS_CAPTURED:
                if ($chargeBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_CAPTURED) {
                    $this->debugLog('Charge with id: ' . $charge->getChargeId() . ' has switched to state CAPTURED', __CLASS__);
                    // a charge is now captured - this implies an incoming payment, only do something if we should set the incoming payments
                    if($this->config->isAddIncomingPayments()) {
                        if($chargeBefore->getShopOrderId() !== null) {
                            // New since 1.2.x - charges know which shop order id they belong to, recurring order charges need this, in particular
                            $order = new Bestellung((int) $chargeBefore->getShopOrderId(), true);
                        } else {
                            $order = $this->database->loadJtlOrderForChargePermissionId($chargeBefore->getChargePermissionId());
                        }
                        if (null !== $order) {
                            $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId(Plugin::getInstance()));
                            $wasOrderPaidCompletely = $paymentMethodModule->isOrderPaidCompletely($order);
                            $paymentMethodModule->addIncomingPayment($order, (object)[
                                'fBetrag' => (float)$charge->getCaptureAmount()->getAmount(),
                                'cISO' => $charge->getCaptureAmount()->getCurrencyCode(),
                                'cHinweis' => $charge->getChargeId()
                            ]);
                            $isOrderPaidCompletely = $paymentMethodModule->isOrderPaidCompletely($order);
                            if (!$wasOrderPaidCompletely && $isOrderPaidCompletely) {
                                // The order was not paid completely before, but is now. change order status and send confirmation mail.
                                $paymentMethodModule->setOrderStatusToPaid($order);
                                $paymentMethodModule->sendConfirmationMail($order);
                            }
                        } else {
                            $this->debugLog('Failed to set incoming payment for unknown order for charge permission id / charge id: ' . $chargeBefore->getChargePermissionId() . ' / ' . $chargeBefore->getChargeId(), __CLASS__);
                        }
                    }
                }
                break;
            default:
                throw new StatusHandlerException($charge->getStatusDetails()->getState(), StatusHandlerException::CODE_UNEXPECTED_STATUS);
        }
        $charge->setShopOrderId($chargeBefore->getShopOrderId());
        $charge->setChargePermissionId($chargeBefore->getChargePermissionId());
        $this->database->saveCharge($charge);
    }

    /**
     * Creates a new charge (but does not save it or handle it!) or returns null if that failed for any reason.
     * @param Charge $charge
     * @return null|Charge
     */
    private function createNewCharge(Charge $charge): ?Charge {
        // the payment failed but may work on retry with a new charge. However, this is only possible if the chargePermission is in CHARGEABLE state, else this is basically a hard decline.
        $chargePermission = $this->adapter->execute(new GetChargePermission($charge->getChargePermissionId()));
        if ($chargePermission instanceof Error) {
            // failed to get the charge permission... give up.
            $this->debugLog('Failed to load charge permission while trying to re-charge a declined charge. Error: ' . $chargePermission->getReasonCode(), __CLASS__);
            return null;
        }
        /** @var ChargePermission $chargePermission */
        // handle this charge permission while we are at it.
        $this->handleChargePermission($chargePermission);
        // check the state of the charge permission
        if ($chargePermission->getStatusDetails()->getState() === StatusDetails::STATUS_CHARGEABLE) {
            // we are in luck and can try another charge
            $createCharge = new CreateCharge($charge->getChargePermissionId(), $charge->getChargeAmount(), $this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE, $this->config->getAuthorizationMode() === Config::AUTHORIZATION_MODE_OMNI);
            $newCharge = $this->adapter->execute($createCharge);
            if ($newCharge instanceof Error) {
                // we failed to create a new charge... give up.
                $this->debugLog('Failed to create new charge while trying to re-charge a declined charge. Error: ' . $newCharge->getReasonCode(), __CLASS__);
                return null;
            }
            // we created a new charge, save the old one, then call ourselves immediately, but ignore that we are not known
            /** @var Charge $newCharge */
            $newCharge->setShopOrderId($charge->getShopOrderId());
            $newCharge->setChargePermissionId($charge->getChargePermissionId());
            return $newCharge;
        }
        // no dice, the charge permission is not chargeable, give up.
        return null;
    }

    public function handleRefund(Refund $refund, bool $isNewRefund = false): void {
        $refundBefore = $this->database->loadRefund($refund->getRefundId());
        if (null === $refundBefore && !$isNewRefund) {
            $this->debugLog('No existing refund found for refund id:' . $refund->getRefundId(), __CLASS__);
            // Unknown refund - do not handle this.
            throw new StatusHandlerException($refund->getRefundId(), StatusHandlerException::CODE_UNKNOWN_OBJECT);
        }
        if (null === $refundBefore && $isNewRefund) {
            // fake the refundBefore to be a copy of the refund we got, but set the copy to the initial state so we handle it correctly in the following steps.
            $refundBefore = (new Refund())->fillFromDatabaseObject($refund->getDatabaseObject());
            $statusDetails = new StatusDetails();
            $statusDetails->setState(StatusDetails::STATUS_REFUND_INITIATED);
            $refundBefore->setStatusDetails($statusDetails);
        }
        switch ($refund->getStatusDetails()->getState()) {
            case StatusDetails::STATUS_REFUND_INITIATED:
                if ($refundBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_REFUND_INITIATED) {
                    // this should not happen as this would mean the refund returned to its initial state
                    $this->debugLog('Refund with id: ' . $refund->getRefundId() . ' has switched to state REFUND_INITIATED which should NOT be possible.', __CLASS__);
                    throw new StatusHandlerException($refund->getStatusDetails()->getState(), StatusHandlerException::CODE_UNEXPECTED_STATUS);
                }
                break;
            case StatusDetails::STATUS_REFUNDED:
                if ($refundBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_REFUNDED) {
                    // ok, the refund was successful. We take note, but there is nothing to do for us.
                    $this->debugLog('Refund with id: ' . $refund->getRefundId() . ' has switched to state REFUNDED.', __CLASS__);
                }
                break;
            case StatusDetails::STATUS_DECLINED:
                if ($refundBefore->getStatusDetails()->getState() !== StatusDetails::STATUS_DECLINED) {
                    // the refund was NOT successful. We might need to inform the merchant.
                    $this->debugLog('Refund with id: ' . $refund->getRefundId() . ' has switched to state DECLINED.', __CLASS__);
                }
                break;
            default:
                throw new StatusHandlerException($refund->getStatusDetails()->getState(), StatusHandlerException::CODE_UNEXPECTED_STATUS);
        }
        $refund->setChargeId($refundBefore->getChargeId());
        $this->database->saveRefund($refund);
    }

    private function handleMerchantNotification($chargePermissionId, $message): void {
        $orderNumber = '-';
        try {
            // Inform Merchant about things that may be important to know.
            $mailer = Shop::Container()->get(Mailer::class);
            $mail = new Mail();
            /** @var Bestellung $jtlOrder */
            $jtlOrder = $this->database->loadJtlOrderForChargePermissionId($chargePermissionId);
            if (null !== $jtlOrder) {
                $orderNumber = $jtlOrder->cBestellNr;
            }
            $data = new \stdClass();
            $data->cBestellNr = $orderNumber;
            $data->chargePermissionId = $chargePermissionId;
            $data->message = $message;
            $config = Shop::getSettings([\CONF_EMAILS]);
            $data->toName = $config['emails']['email_master_absender_name'] ?? '';
            $data->toMail = $config['emails']['email_master_absender'] ?? '';
            $data->mail = new \stdClass();
            $data->mail->toName = $data->toName;
            $data->mail->toMail = $data->toMail;
            $mail->setToMail($data->toMail);
            $mail->setToName($data->toName);
            $mail = $mail->createFromTemplateID('kPlugin_' . $this->plugin->getID() . '_amazonpayinfo', $data);
            $mailer->send($mail);
        } catch (Exception $ex) {
            $this->errorLog('Failed to send merchant notificiation for ' . $chargePermissionId . ' and order number ' . $orderNumber . ' with message: "'.  $message .'" Exception: ' . $ex->getMessage() . "\n" . $ex->getTraceAsString(), __CLASS__);
        }
    }

    private function handleHardDecline($chargePermissionId): void {
        try {
            // Inform Buyer, that he cannot pay with Amazon Pay, after all and has to contact the merchant.
            $mailer = Shop::Container()->get(Mailer::class);
            $mail = new Mail();

            /** @var Bestellung $jtlOrder */
            $jtlOrder = $this->database->loadJtlOrderForChargePermissionId($chargePermissionId);
            if (null === $jtlOrder) {
                $this->debugLog('Could not send hard decline for Amazon order reference id ' . $chargePermissionId . ' because order was not found.', __CLASS__);
                return;
            }
            $customer = new Customer((int)$jtlOrder->kKunde);
            $data = new \stdClass();
            $data->tkunde = $customer;
            $data->order = $jtlOrder;
            $mail = $mail->createFromTemplateID('kPlugin_' . $this->plugin->getID() . '_amazonpayharddecline', $data);
            $mailer->send($mail);
        } catch (Exception $ex) {
            $this->errorLog('Failed to handle hard decline for order reference id ' . $chargePermissionId . '. Please inform the customer that he cannot pay with Amazon Pay! Exception: ' . $ex->getMessage() . "\n" . $ex->getTraceAsString(), __CLASS__);
        }
    }

    /**
     * Refreshes a charge permission against amazon and handles changes
     * @param $chargePermissionId
     * @param bool $cascade
     * @throws \Exception
     * @throws TechnicalException
     */
    public function performRefreshForChargePermission($chargePermissionId, bool $cascade = true): void {
        $chargePermission = $this->adapter->execute(new GetChargePermission($chargePermissionId));
        if ($chargePermission instanceof Error) {
            throw new TechnicalException($chargePermission->getReasonCode(), $chargePermission->getHttpErrorCode());
        }
        /** @var ChargePermission $chargePermission */
        $this->handleChargePermission($chargePermission);
        if ($cascade) {
            $charges = $this->database->loadChargesForChargePermission($chargePermission->getChargePermissionId());
            foreach ($charges as $charge) {
                /** @var Charge $charge */
                $this->performRefreshForCharge($charge->getChargeId(), true);
            }
        }
    }

    /**
     * @param $chargeId
     * @param bool $cascade
     * @throws \Exception
     * @throws TechnicalException
     */
    public function performRefreshForCharge($chargeId, bool $cascade = true): void {
        $charge = $this->adapter->execute(new GetCharge($chargeId));
        if ($charge instanceof Error) {
            throw new TechnicalException($charge->getReasonCode(), $charge->getHttpErrorCode());
        }
        /** @var Charge $charge */
        $this->handleCharge($charge);
        if ($cascade) {
            $refunds = $this->database->loadRefundsForCharge($charge->getChargeId());
            foreach ($refunds as $refund) {
                /** @var Refund $refund */
                $this->performRefreshForRefund($refund->getRefundId());
            }
        }
    }

    /**
     * @param $refundId
     * @throws \Exception
     * @throws TechnicalException
     */
    public function performRefreshForRefund($refundId): void {
        $refund = $this->adapter->execute(new GetRefund($refundId));
        if ($refund instanceof Error) {
            throw new TechnicalException($refund->getReasonCode(), $refund->getHttpErrorCode());
        }
        /** @var Refund $refund */
        $this->handleRefund($refund);
    }


}