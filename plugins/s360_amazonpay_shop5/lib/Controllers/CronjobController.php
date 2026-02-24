<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use DateTime;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Shop;
use JTL\Customer\Customer;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;

/**
 * Class CronjobController
 *
 * Handles running of the cronjob itself.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class CronjobController {

    use JtlLoggerTrait;

    // wait for 1 seconds between calls in sandbox, and 0.5 seconds between calls in production.
    private const WAIT_TIME_SANDBOX_US = 1000000;
    private const WAIT_TIME_PRODUCTION_US = 500000;
    // run at most once per hour
    private const INTERVAL = 3600;
    // factor to prevent running over the max_execution_time (this might not work due to other scripts being called as well, outside of our scope)
    private const MAX_EXECUTION_TIME_FACTOR = 0.75;
    // default max_execution_time for php is 30 seconds.
    private const DEFAULT_PHP_MAX_EXECUTION_TIME_SECONDS = 30;
    private const DAY = 86400;
    private const MAX_AUTO_RETRY_DAYS_ON_STOCK_LEVEL_ZERO = 2;

    private $internalMaxExecutionTime;
    private $database;
    private $config;
    private $plugin;
    private $apiCallTimeout;

    public function __construct() {
        $this->database = Database::getInstance();
        $this->config = Config::getInstance();
        // Try to set our internal max execution time to a fraction of what is configured for PHP, but max 1 hour - this is hopefully conservative enough to prevent any time overruns.
        $configuredMaxExecutionTime = ini_get('max_execution_time');
        if (!empty($configuredMaxExecutionTime)) {
            $this->internalMaxExecutionTime = min((int)((float)$configuredMaxExecutionTime * self::MAX_EXECUTION_TIME_FACTOR), self::INTERVAL);
        } elseif ($configuredMaxExecutionTime === '0') {
            $this->internalMaxExecutionTime = self::INTERVAL; // on infinite max execution time make sure we do not run longer than an hour
        } else {
            $this->internalMaxExecutionTime = (int)(self::DEFAULT_PHP_MAX_EXECUTION_TIME_SECONDS * self::MAX_EXECUTION_TIME_FACTOR); // decide based on default max_execution_time
        }
        // Determine needed timeout between calls depending on environment, this is needed to avoid additional waiting times due to throttling
        $this->apiCallTimeout = ($this->config->getEnvironment() === Config::ENVIRONMENT_SANDBOX ? self::WAIT_TIME_SANDBOX_US : self::WAIT_TIME_PRODUCTION_US);
        $this->plugin = Plugin::getInstance();
    }

    /**
     * Runs the cronjob. Note that the job itself controls how many objects it actually handles.
     * It tries to prevent running beyond the max_execution_time set.
     */
    public function run(): void {
        /*
         * GetChargePermission:
         *
         * This was true for GetOrderReferenceDetails, maybe it sill applies:
         *
         * This operation has a maximum request quota of 20 and a restore rate of two requests every second in the production environment - so basically, it is max 2 per second allowed in production.
         * It has a maximum request quota of five and a restore rate of one request every second in the sandbox environment - so basically, it is max 1 per second allowed in sandbox.
         *
         * Note that a single order refresh also refreshes all additional objects on it.
         * Also note that while this is running there may be further calls in the frontend, so we must not max out the throttling limit here!
         */
        if (!$this->isTimeToRun()) {
            return;
        }

        $now = time();
        $this->config->setLastCronRunTimestamp($now);

        // Handle creation of subscription orders
        if ($this->config->getSubscriptionMode() !== Config::SUBSCRIPTION_MODE_INACTIVE) {
            $this->handleSubscriptionRenewals();
            $this->handleSubscriptionPrenotification();
        }

        // Handle all charge permissions that are not in a final state
        $unfinishedChargePermissionIds = $this->getUnfinishedChargePermissionIds();
        $this->debugLog('Starting cron run for ' . count($unfinishedChargePermissionIds) . ' unfinished charge permissions with maximum run time of ' . $this->internalMaxExecutionTime . ' seconds.', __CLASS__);
        if (!empty($unfinishedChargePermissionIds)) {
            $timeLimit = $now + $this->internalMaxExecutionTime;
            $statusController = new StatusController();
            $errorMessages = [];
            foreach ($unfinishedChargePermissionIds as $unfinishedChargePermissionId) {
                if (time() > $timeLimit) {
                    $this->debugLog('Finishing run to avoid max_execution_time timeout.', __CLASS__);
                    return; // Time is up but we are not done - no problem, though. Calling the run() method again will continue the work.
                }
                $this->debugLog('Refreshing unfinished order reference ' . $unfinishedChargePermissionId, __CLASS__);
                try {
                    $statusController->performRefreshForChargePermission($unfinishedChargePermissionId, true);
                } catch(TechnicalException $ex) {
                    $errorMessages[] = 'ChargePermission "'. $unfinishedChargePermissionId.'": ' . $ex->getCode() . ': '. $ex->getMessage();
                }
                /** @noinspection DisconnectedForeachInstructionInspection */
                usleep($this->apiCallTimeout);
            }
        }
        if(!empty($errorMessages)) {
            $this->errorLog('Cronjob: Failed to refresh the following charge permissions with errors:' . implode(", \n", $errorMessages), __CLASS__);
        }
        $this->debugLog('Finished run.', __CLASS__);
    }

    /**
     * Returns all chargePermissionIds where applies one of the following:
     * - A refund that is RefundInitiated
     * - A charge that is AuthorizationInitated, Authorized or CaptureInitiated
     * - A status that is Chargeable or NonChargeable
     * @return array
     */
    private function getUnfinishedChargePermissionIds(): array {
        $refunds = $this->database->getRefundsByState([StatusDetails::STATUS_REFUND_INITIATED], true);
        $chargeIdsForUnfinishedRefunds = array_map(function ($refund) {
            return $refund->chargeId;
        }, $refunds);
        unset($refunds);

        $charges = $this->database->getChargesByState([StatusDetails::STATUS_AUTHORIZATION_INITIATED, StatusDetails::STATUS_AUTHORIZED, StatusDetails::STATUS_CAPTURE_INITIATED], true);
        $chargeIdsForUnfinishedCaptures = array_map(function ($charge) {
            return $charge->chargeId;
        }, $charges);

        // load charges that are finished themselves but have unfinished refunds
        foreach (array_diff($chargeIdsForUnfinishedRefunds, $chargeIdsForUnfinishedCaptures) as $chargeId) {
            $charges[] = $this->database->loadCharge($chargeId, true);
        }

        $chargePermissionIdsForUnfinishedCharges = array_map(function ($charge) {
            return $charge->chargePermissionId;
        }, $charges);
        unset($charges);

        // load order references
        $chargePermissions = $this->database->getChargePermissionsByState([StatusDetails::STATUS_CHARGEABLE, StatusDetails::STATUS_NON_CHARGEABLE], true);
        $chargePermissionIds = array_map(function ($chargePermission) {
            return $chargePermission->chargePermissionId;
        }, $chargePermissions);

        // return all charge permission ids that need refreshing
        return array_unique(array_merge($chargePermissionIdsForUnfinishedCharges, $chargePermissionIds));
    }

    private function isTimeToRun(): bool {
        if ($this->config->getCronMode() === Config::CRON_MODE_TASK) {
            // in this case JTL Shop handles calling us - we always run when this is configured and we are called.
            return true;
        }
        $now = time();
        $lastCronRunTimestamp = $this->config->getLastCronRunTimestamp();
        return $lastCronRunTimestamp === null || (($now - self::INTERVAL) >= $lastCronRunTimestamp);
    }

    private function handleSubscriptionPrenotification(): void {
        // Ensure that we do not send these more than once per subscription per order by running at max once per day
        $now = time();

        $todayStartOfDay = new DateTime();
        $todayStartOfDay->setTimestamp($now);
        $todayStartOfDay->setTime(0, 0, 0);

        $todayEndOfDay = new DateTime();
        $todayEndOfDay->setTimestamp($now);
        $todayEndOfDay->setTime(23, 59, 59);

        $lastTimestamp = $this->config->getLastSubscriptionPrenotificationTimestamp();
        if ($lastTimestamp === null || $lastTimestamp < $todayStartOfDay->getTimestamp()) {
            // we should run now, update the last run timestamp first so we are sure to only run once today
            $this->config->setLastSubscriptionPrenotificationTimestamp($now);
            $prenotificationDays = $this->config->getSubscriptionReminderMailLeadTimeDays();
            if (empty($prenotificationDays) || $prenotificationDays < 0) {
                return; // prenotifications are disabled
            }
            try {
                $daysInterval = new \DateInterval('P' . (string)$prenotificationDays . 'D');
            } catch (\Exception $e) {
                // Failed to create a date interval
                $this->debugLog('Invalid date interval for prenotification days. Skipping Prenotifications.', __CLASS__);
                return;
            }

            // As we are handling days here, we want to sent notifications only for those orders that are prenotificationDays days in the future from *today*
            // This way we are also sure that the subscription's actual timestamp will fall into just one run of us.
            $lowerTimestampLimit = $todayStartOfDay->add($daysInterval)->getTimestamp();
            $upperTimestampLimit = $todayEndOfDay->add($daysInterval)->getTimestamp();

            // Get subscriptions where the next order timestamp falls into the limits and send mail to customers
            $subscriptions = $this->database->getSubscriptionsDueBetween($lowerTimestampLimit, $upperTimestampLimit);
            foreach ($subscriptions as $subscription) {
                /** @var Subscription $subscription */
                $customerId = $subscription->getJtlCustomerId();
                if (empty($customerId)) {
                    // no customer id present
                    continue;
                }
                $customer = new Customer((int)$customerId);
                if (empty($customer->kKunde) || empty($customer->cMail)) {
                    // failed to load customer
                    continue;
                }
                $data = new \stdClass();
                $data->tkunde = $customer;
                $data->amazonPaySubscription = $subscription;
                $mailer = Shop::Container()->get(Mailer::class);
                $mail = new Mail();
                $mailer->send($mail->createFromTemplateID('kPlugin_' . $this->plugin->getID() . '_' . Constants::MAIL_TEMPLATE_SUBSCRIPTION_REMINDER, $data));
            }
        }
    }

    private function handleSubscriptionRenewals(): void {
        // Use the SubscriptionController to renew subscriptions that are due for renewal (i.e. which are active and which have a next order timestamp that is in the past - note that next order timestamps that are in the past more than the selected interval might pose a problem!).
        $now = time();
        $subscriptions = $this->database->getSubscriptionsDueBetween(0, $now);
        if (empty($subscriptions)) {
            return;
        }
        $subscriptionController = new SubscriptionController($this->plugin);
        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            try {
                $subscriptionController->createOrderForSubscription($subscription->getId());
                $subscriptionController->renewSubscription($subscription->getId());
            } catch (\Exception $ex) {
                try {
                    // Handle different exceptions that may occur when trying to renew a subscription
                    if ($ex->getCode() === Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE) {
                        $this->debugLog('Subscription Review Required - Charge could not be created or loaded for Subscription ID '. $subscription->getId() , __CLASS__);
                        $subscriptionController->setSubscriptionToReview($subscription->getId(), Subscription::REASON_CHARGE_PROBLEM);
                    } elseif ($ex->getCode() === Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE_PERMISSION) {
                        $this->debugLog('Subscription Review Required - Charge Permission could not be loaded for Subscription ID '. $subscription->getId() , __CLASS__);
                        $subscriptionController->setSubscriptionToReview($subscription->getId(), Subscription::REASON_CHARGE_PERMISSION_PROBLEM);
                    } elseif ($ex->getCode() === Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DEACTIVATED) {
                        $this->debugLog('Subscription Review Required - Product in order was deactivated for Subscription ID '. $subscription->getId() , __CLASS__);
                        $subscriptionController->setSubscriptionToReview($subscription->getId(), Subscription::REASON_PRODUCT_DEACTIVATED);
                    } elseif ($ex->getCode() === Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DOES_NOT_EXIST) {
                        $this->debugLog('Subscription Review Required - Product in order does not exist anymore for Subscription ID '. $subscription->getId() , __CLASS__);
                        $subscriptionController->setSubscriptionToReview($subscription->getId(), Subscription::REASON_PRODUCT_DOES_NOT_EXIST);
                    } elseif ($ex->getCode() === Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_STOCK_LEVELS) {
                        // We failed to create a new subscription because of stock levels having run to 0. It may be advisable to NOT stop the subscription in this case because the stock level may automatically recover
                        // Let's see for how long this subscription could not be recreated
                        if($subscription->getNextOrderTimestamp() + (self::DAY * self::MAX_AUTO_RETRY_DAYS_ON_STOCK_LEVEL_ZERO) < $now) {
                            $this->debugLog('Subscription Review Required - Product in order does not have stock levels for Subscription ID ' . $subscription->getId(), __CLASS__);
                            $subscriptionController->setSubscriptionToReview($subscription->getId(), Subscription::REASON_PRODUCT_STOCK_LEVELS);
                        } else {
                            $this->debugLog('Subscription Potential Review Required - Product in order does not have stock levels for Subscription ID ' . $subscription->getId() .'. Retrying to create order for up to ' . self::MAX_AUTO_RETRY_DAYS_ON_STOCK_LEVEL_ZERO .' days before setting it to Review status.', __CLASS__);
                        }
                    } else {
                        // not recoverable - we need to stop the subscription
                        $this->debugLog('Subscription Renewal Failed - Canceling subscription with unrecoverable exception for Subscription ID ' . $subscription->getId(), __CLASS__);
                        $subscriptionController->cancelSubscription($subscription->getId(), Subscription::REASON_UNRECOVERABLE_EXCEPTION);
                    }
                } catch (\Exception $ex) {
                    // This is not good - we failed to cancel or set the subscription to review
                    $this->errorLog('Failed to change subscription status for Subscription ID: ' . $subscription->getId(), __CLASS__);
                }
            }
        }
    }
}