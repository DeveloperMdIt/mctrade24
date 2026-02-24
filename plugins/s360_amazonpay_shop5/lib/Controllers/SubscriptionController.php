<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use DateTime;
use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Checkout\OrderHandler; // Class exists only in 5.2.0+
use JTL\Checkout\StockUpdater; // Class exists only in 5.2.0+
use JTL\Customer\Customer;
use JTL\Events\Dispatcher;
use JTL\Helpers\Date;
use JTL\Helpers\Request;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CaptureCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CloseChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\UpdateChargePermission;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\Events;
use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * This controller handles backend logic related to recurring payments / subscriptions.
 *
 * @class SubscriptionController
 */
class SubscriptionController {
    use JtlLoggerTrait;

    protected $config;
    protected $database;
    protected $adapter;
    protected $plugin;

    public function __construct(PluginInterface $plugin) {
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->adapter = new ApiAdapter();
        $this->plugin = $plugin;
    }

    /**
     * @param int $shopOrderId
     * @param int $jtlCustomerId
     * @param string $chargePermissionId
     * @param Interval $interval
     * @return Subscription|null
     * @throws Exception
     */
    public function addSubscription(int $shopOrderId, string $chargePermissionId, Interval $interval): ?Subscription {
        $shopOrder = new Bestellung($shopOrderId, true);
        if ($shopOrder->kBestellung <= 0) {
            $this->debugLog('AddSubscription: Failed to load order with kBestellung ' . $shopOrderId, __CLASS__);
            throw new Exception('AddSubscription: Failed to load order with kBestellung ' . $shopOrderId, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        $jtlCustomer = new Customer((int)$shopOrder->kKunde);

        if ($jtlCustomer->getID() <= 0) {
            $this->debugLog('AddSubscription: Failed to add subscription for missing customer id kKunde ' . (int)$shopOrder->kKunde, __CLASS__);
            throw new Exception('AddSubscription: Failed to add subscription for missing customer id kKunde ' . (int)$shopOrder->kKunde, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        $subscription = new Subscription();
        $subscription->setShopOrderId($shopOrderId);
        $subscription->setShopOrderNumber($shopOrder->cBestellNr);
        $subscription->setJtlCustomerId($jtlCustomer->getID());
        $subscription->setChargePermissionId($chargePermissionId);
        $subscription->setStatus(Subscription::STATUS_ACTIVE);
        $subscription->setStatusReason('');
        $subscription->setInterval($interval);
        $lastOrderTimestamp = (new DateTime($shopOrder->dErstellt))->getTimestamp();
        $subscription->setLastOrderTimestamp($lastOrderTimestamp);
        $nextOrderTimestamp = $interval->addToTimestamp($lastOrderTimestamp);
        if ($this->config->isSubscriptionNormalizeOrderTime()) {
            $nextOrderTimestamp = $this->normalizeTimestampTime($nextOrderTimestamp, $this->config->getSubscriptionNormalizeOrderTimeTo());
        }
        $subscription->setNextOrderTimestamp($nextOrderTimestamp);
        $subscriptionWithId = $this->database->upsertSubscription($subscription);
        if ($subscriptionWithId === null) {
            $this->debugLog('AddSubscription: Failed to save new subscription to database.', __CLASS__);
            if (defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
                $this->debugLog(print_r($subscription, true), __CLASS__);
            }
            throw new Exception('AddSubscription: Failed to save new subscription to database.', Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }

        // add mapping for this subscription to its order, too - note that this is an initial subscription and therefore both, the order id and initial order id are the same.
        $this->database->addSubscriptionOrder($subscriptionWithId->getId(), $subscriptionWithId->getShopOrderId(), $subscriptionWithId->getShopOrderId());

        // set order attribute
        $this->database->saveSubscriptionOrderAttribute($shopOrder, $subscriptionWithId, Constants::SUBSCRIPTION_ORDERATTRIBUTE_FLAG_NEW);

        Dispatcher::getInstance()->fire(Events::AFTER_SUBSCRIPTION_CREATED, ['subscriptionId' => $subscriptionWithId->getId(), 'subscription' => $subscriptionWithId, 'order' => $shopOrder, 'customer' => $jtlCustomer]);

        return $subscriptionWithId;
    }

    /**
     * @throws Exception
     */
    public function cancelSubscriptionForCustomer(int $customerId, int $subscriptionId, string $reason = ''): ?Subscription {
        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        if ($subscription->getJtlCustomerId() !== $customerId) {
            $this->debugLog('Failed to cancel subscription for customer as the customer id is mismatched.', __CLASS__);
            throw new Exception('Failed to cancel subscription for customer as the customer id is mismatched.', Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        return $this->cancelSubscription($subscription->getId(), $reason);
    }

    /**
     * @throws Exception
     */
    public function cancelSubscription(int $subscriptionId, string $reason = ''): ?Subscription {
        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        $subscription->setStatus(Subscription::STATUS_CANCELED);
        $subscription->setStatusReason($reason);

        if ($reason === Subscription::REASON_ACCOUNT_DELETED) {
            // in the case of customer deletion we should also delete the ID from our mappings just in case it gets re-assigned later
            $subscription->setJtlCustomerId(0);
        }

        $result = $this->database->upsertSubscription($subscription);
        if($reason !== Subscription::REASON_CHARGE_PERMISSION_CLOSED) {
            try {
                // Also cancel subscription against AMAZON PAY by Closing the Charge Permission! We must be careful not to trigger this if we actually came here *because of* a closed charge permission, or we will unnecessarily refresh data or even worse, create an infinite loop.
                $chargePermissionId = $subscription->getChargePermissionId();
                $apiAdapter = new ApiAdapter();
                $chargePermission = $apiAdapter->execute(new CloseChargePermission($chargePermissionId, $reason));
                if ($chargePermission instanceof Error) {
                    throw new Exception($chargePermission->getReasonCode());
                }
                $statusController = new StatusController();
                $statusController->performRefreshForChargePermission($chargePermissionId, true);
            } catch (Exception $ex) {
                $this->debugLog('Failed to close charge permission on cancel of subscription (' . $subscriptionId . ') - ignoring because it has no further consequences. Message: ' . $ex->getMessage(), __CLASS__);
            }
        }

        Dispatcher::getInstance()->fire(Events::AFTER_SUBSCRIPTION_CANCELED, ['subscriptionId' => $subscriptionId, 'subscription' => $subscription, 'reason' => $reason]);
        return $result;
    }

    /**
     * @throws Exception
     */
    public function cancelAllSubscriptionsForCustomer(int $jtlCustomerId, $reason = ''): void {
        $subscriptions = $this->database->selectSubscriptionsByCustomerId($jtlCustomerId);
        if (empty($subscriptions)) {
            return;
        }
        foreach ($subscriptions as $subscription) {
            $this->cancelSubscription($subscription->getId(), $reason);
        }
    }

    /**
     * @throws Exception
     */
    public function renewSubscription(int $subscriptionId, bool $fromNow = false): ?Subscription {
        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Renew Subscription: Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Renew Subscription: Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        if ($subscription->getInterval() === null) {
            $this->debugLog('Renew Subscription: Failed to renew subscription with id ' . $subscriptionId . ' because it has no interval.', __CLASS__);
            throw new Exception('Renew Subscription: Failed to renew subscription with id ' . $subscriptionId . ' because it has no interval.', Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        $now = time();
        if ($fromNow) {
            $subscription->setLastOrderTimestamp($now);
            $nextOrderTimestamp = $subscription->getInterval()->addToTimestamp($now);
        } else {
            $subscription->setLastOrderTimestamp($subscription->getNextOrderTimestamp());
            // we might have skipped some iterations due to reviews or the likes, so we have to add the timestamp until we are beyond "now", or else the subscription will trigger multiple times in a row now.
            $nextOrderTimestamp = $subscription->getInterval()->addToTimestamp($subscription->getNextOrderTimestamp());
            while($nextOrderTimestamp < $now) {
                $nextOrderTimestamp = $subscription->getInterval()->addToTimestamp($nextOrderTimestamp);
            }
        }
        if ($this->config->isSubscriptionNormalizeOrderTime()) {
            $nextOrderTimestamp = $this->normalizeTimestampTime($nextOrderTimestamp, $this->config->getSubscriptionNormalizeOrderTimeTo());
        }
        $subscription->setNextOrderTimestamp($nextOrderTimestamp);
        return $this->database->upsertSubscription($subscription);
    }

    /**
     * Creates the new order from a given subscription id.
     *
     * @param int $subscriptionId
     * @return Bestellung the id of the created order
     * @throws Exception
     */
    public function createOrderForSubscription(int $subscriptionId) {

        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';

        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId(Plugin::getInstance()));

        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Create Order for Subscription: Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Create Order for Subscription: Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }

        // Load charge permission from amazon pay and validate that it is still open
        $apiAdapter = new ApiAdapter();
        $getChargePermission = new GetChargePermission($subscription->getChargePermissionId());
        try {
            $chargePermission = $apiAdapter->execute($getChargePermission);
            if ($chargePermission instanceof Error) {
                throw new Exception('Create Order for Subscription: Failed to retrieve charge permission with error: ' . $chargePermission->getReasonCode(), Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE_PERMISSION);
            }
        } catch (Exception $ex) {
            $this->debugLog($ex->getMessage(), __CLASS__);
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        /** @var $chargePermission ChargePermission */
        if ($chargePermission->getStatusDetails()->getState() !== StatusDetails::STATUS_CHARGEABLE) {
            $chargePermissionStatus = $chargePermission->getStatusDetails()->getState();
            // the charge permission cannot be charged - we cannot perform a new order - this *might* resolve iff the status is NON_CHARGEABLE. All other status do not allow us to recover this later.
            $this->debugLog('Create Order for Subscription: Failed because charge permission is not in a chargeable state. (' . $chargePermission->getStatusDetails()->getState() . ')' , __CLASS__);
            throw new Exception('Create Order for Subscription: Failed because charge permission is not in a chargeable state. (' . $chargePermission->getStatusDetails()->getState() . ')', $chargePermissionStatus === StatusDetails::STATUS_NON_CHARGEABLE ? Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE_PERMISSION : Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }

        /**
         * Recreating an order is done by duplicating database entries.
         *
         * The order of creation must be the following, according to finalisiereBestellung:
         * - customer is getting set to cAbgeholt = N (but we do not change the customer, so we might not do this)
         * - delivery address is written to the DB if it is new (but we do not change the delivery address, so we will not do this)
         * - kKunde is set on the cart (as this does not change, we leave this be)
         * - kLieferadresse is set on the cart (as this does not change, we leave this be)
         * - the cart is inserted into the db => kWarenkorb created
         * - cart positions are inserted into the db => kWarenkorbPos created (using kWarenkorb)
         * - cart position properties are inserted into the db (using kWarenkorbPos)
         * - aktualisiereLagerbestand is used to update the stock level of products
         * - billing address is inserted into the db => kRechnungsadresse
         * - order gets the new keys and is inserted into the db
         * - order attributes are inserted into the db
         * - tbestellstatus and tbestellid get new entries
         * - coupon usages are recalculated
         */
        $order = $this->database->getShopOrder($subscription->getShopOrderId());
        if (empty($order)) {
            $this->debugLog('Create Order for Subscription: Failed to load order by id for id ' . $subscription->getShopOrderId(), __CLASS__);
            throw new Exception('Create Order for Subscription: Failed to load order by id for id ' . $subscription->getShopOrderId(), Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        $customer = $this->database->getShopCustomer($subscription->getJtlCustomerId());
        if (empty($customer)) {
            $this->debugLog('Create Order for Subscription: Failed to load customer by id for id ' . $subscription->getJtlCustomerId(), __CLASS__);
            throw new Exception('Create Order for Subscription: Failed to load customer by id for id ' . $subscription->getJtlCustomerId(), Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        if ($customer->kKunde !== $order->kKunde) {
            $this->debugLog('Create Order for Subscription: Mismatch between customer id in subscription (' . $customer->kKunde . ') and customer id in order (' . $order->kKunde . '). Aborting.', __CLASS__);
            throw new Exception('Create Order for Subscription: Mismatch between customer id in subscription and customer id in order.', Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }

        // These are loaded but NOT duplicated, only validated.
        $shippingMethod = $this->database->getShopShippingMethod((int)$order->kVersandart);
        if (empty($shippingMethod)) {
            $this->debugLog('Create Order for Subscription: Shipping method does not exist anymore (' . (int)$order->kVersandart . '). Aborting.', __CLASS__);
            throw new Exception('Create Order for Subscription: Shipping method does not exist anymore (' . (int)$order->kVersandart . ').', Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        $currency = new \JTL\Catalog\Currency((int)$order->kWaehrung);
        if (empty($currency->getCode())) {
            $this->debugLog('Create Order for Subscription: Currency does not exist anymore (' . (int)$order->kWaehrung . '). Aborting.', __CLASS__);
            throw new Exception('Create Order for Subscription: Currency does not exist anymore (' . (int)$order->kWaehrung . ').', Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }

        // Billing address will be duplicated later
        $billingAddress = (int)$order->kRechnungsadresse > 0 ? $this->database->getShopBillingAddress((int)$order->kRechnungsadresse) : null;

        $cart = (int)$order->kWarenkorb > 0 ? $this->database->getShopCart((int)$order->kWarenkorb) : null;
        $cartPositions = (int)$order->kWarenkorb > 0 ? $this->database->getShopCartPositions((int)$order->kWarenkorb) : [];
        $cartPositionProperties = $this->database->getShopCartPositionProperties($cartPositions);

        /**
         * Build an array similar to Cart->PositionenArr, but only with products and gift products.
         * This array is needed to update the stock positions later and to calculate the availability strings for the confirmation mail.
         */
        $cartProductPositions = [];
        foreach ($cartPositions as $cartPosition) {
            $type = (int)$cartPosition->nPosTyp;
            if ($type === C_WARENKORBPOS_TYP_ARTIKEL || $type === C_WARENKORBPOS_TYP_GRATISGESCHENK) {
                $cartPositionObject = clone $cartPosition;
                $cartPositionObject->Artikel = new Artikel();
                $cartPositionObject->Artikel->fuelleArtikel((int)$cartPositionObject->kArtikel, Artikel::getDetailOptions(), (int)$customer->kKundengruppe, (int)$customer->kSprache);
                $productId = $cartPositionObject->Artikel->getID();
                if ($productId === null || $productId <= 0) {
                    // the product could not be loaded - we need to abort here
                    $this->debugLog('Create Order for Subscription: Product in order does not exist anymore (' . (int)$cartPositionObject->kArtikel . '). Aborting.', __CLASS__);
                    throw new Exception('Create Order for Subscription: Product in order does not exist anymore (' . (int)$cartPositionObject->kArtikel . ').', Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DOES_NOT_EXIST); // this *may* be recovered by re-enabling the product
                }
                // Add WarenkorbPosEigenschaftArr
                $cartPositionPropertiesArray = \array_filter($cartPositionProperties, static function ($property) use ($cartPositionObject) {
                    return ((int)$property->kWarenkorbPos === (int)$cartPositionObject->kWarenkorbPos);
                });
                if (!empty($cartPositionPropertiesArray)) {
                    $cartPositionObject->WarenkorbPosEigenschaftArr = $cartPositionPropertiesArray;
                }
                $cartProductPositions[] = $cartPositionObject;
            } elseif ($type === C_WARENKORBPOS_TYP_NEUKUNDENKUPON || $type === C_WARENKORBPOS_TYP_GUTSCHEIN || $type === C_WARENKORBPOS_TYP_KUPON) {
                // the cart contains a coupon or voucher, neither of which we can support
                $this->debugLog('Create Order for Subscription: Cart contains unsupported position type (' . $type . '). Aborting.', __CLASS__);
                throw new Exception('Create Order for Subscription: Cart contains unsupported position type (' . $type . ').', Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
            }
        }

        // Validate the data we collected.
        // Check if the order used store credit
        if ((float)$order->fGuthaben > 0) {
            $this->debugLog('Create Order for Subscription: Order was placed while using store credit. Cannot be reproduced.', __CLASS__);
            throw new Exception('Create Order for Subscription: Order was placed while using store credit. Cannot be reproduced.', Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }

        // Check product availabilities
        if (!$this->checkDeactivatedPositions($cartProductPositions)) {
            // at least one product would be deactivated on checkout
            $this->debugLog('Create Order for Subscription: Product in cart was deactivated / is not available. Aborting.', __CLASS__);
            throw new Exception('Create Order for Subscription: Product in cart was deactivated / is not available. Aborting.', Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_PRODUCT_DEACTIVATED);
        }

        if (!$this->checkStockLevels($cartProductPositions)) {
            // at least one product does not have a stock level
            $this->debugLog('Create Order for Subscription: Product in cart does not have a valid stock level. Aborting.', __CLASS__);
            throw new Exception('Create Order for Subscription: Product in cart does not have a valid stock level.', Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_STOCK_LEVELS);
        }

        // This array is only informative about a longer delivery period for certain products - no need to abort
        $availabilityArray = $this->checkAvailability($cartProductPositions);

        /**
         * FROM HERE ON NEW OBJECTS ARE CREATED
         */
        // We use this array to collect data that has been written to the database along the way but might need to be deleted in case of a roll back
        $rollbackData = [];

        // Create a new charge on the charge permission
        try {
            $price = new Price();
            $price->setAmount(Currency::convertToAmazonString((float)$order->fGesamtsumme * (float)$order->fWaehrungsFaktor)); // IMPORTANT: We must use the orders currency factor, not the current currency factor so that the total amount does not vary!
            $price->setCurrencyCode($currency->getCode());
            // Here we create the actual charge - note that we do not allow pending charges here, ever, to prevent asynchronous soft declines or hard declines.
            $createCharge = new CreateCharge($chargePermission->getChargePermissionId(), $price, false, false, null);
            $charge = $apiAdapter->execute($createCharge);
            if ($charge instanceof Error) {
                throw new Exception('Create Order for Subscription: Failed to create a new charge with error: ' . $charge->getReasonCode(), Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE);
            }
        } catch (Exception $ex) {
            $this->debugLog($ex->getMessage(), __CLASS__);
            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        /** @var $charge Charge */

        // Check if the charge has an ok state.
        $chargeState = $charge->getStatusDetails()->getState();
        if ($chargeState === StatusDetails::STATUS_CANCELED || $chargeState === StatusDetails::STATUS_DECLINED) {
            $this->debugLog('Create Order for Subscription: Charge status is not what we expected. Aborting order. Charge status is: ' . $chargeState, __CLASS__);
            throw new Exception('Create Order for Subscription: Charge status is not what we expected. Aborting order. Charge status is: ' . $chargeState, Constants::SUBSCRIPTION_EXCEPTION_CODE_RECOVERABLE_CHARGE);
        }

        // -------------------- The charge is ok! We can create the order! --------------
        // Let's save this charge
        $charge->setChargePermissionId($chargePermission->getChargePermissionId()); // we need to set this manually as it is not part of the actual amazon pay charge

        $rollbackData['chargeId'] = $charge->getChargeId();
        $this->database->saveCharge($charge);

        // insert new cart
        $newCart = $this->duplicateCart($cart);
        $newCart = $this->database->insertNewCart($newCart);
        if (empty($newCart) || empty($newCart->kWarenkorb)) {
            $this->debugLog('Create Order for Subscription: Failed to insert cart into database. Aborting order.', __CLASS__);
            $this->rollbackOrderCreation($rollbackData);
            throw new Exception('Create Order for Subscription: Failed to insert cart into database. Aborting order.', Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        $rollbackData['cartId'] = (int)$newCart->kWarenkorb;

        $rollbackData['cartPositionIds'] = [];
        $cartPositionIdMappings = [];
        foreach ($cartPositions as $cartPosition) {
            $newCartPosition = $this->duplicateCartPosition($cartPosition, (int)$newCart->kWarenkorb);
            $newCartPosition = $this->database->insertNewCartPosition($newCartPosition);
            if (empty($newCartPosition) || empty($newCartPosition->kWarenkorbPos)) {
                $this->debugLog('Create Order for Subscription: Failed to insert cart position into database. Old ID: ' . $cartPosition->kWarenkorbPos, __CLASS__);
                $this->rollbackOrderCreation($rollbackData);
                throw new Exception('Create Order for Subscription: Failed to insert cart position into database. Old ID: ' . $cartPosition->kWarenkorbPos, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
            }
            $cartPositionIdMappings[$cartPosition->kWarenkorbPos] = $newCartPosition->kWarenkorbPos;
            $rollbackData['cartPositionIds'][] = (int)$newCartPosition->kWarenkorbPos;
        }
        $rollbackData['cartPositionPropertyIds'] = [];
        foreach ($cartPositionProperties as $cartPositionProperty) {
            $newCartPositionProperty = $this->duplicateCartPositionProperty($cartPositionProperty, (int)$cartPositionIdMappings[$cartPositionProperty->kWarenkorbPos]);
            $newCartPositionProperty = $this->database->insertNewCartPositionProperty($newCartPositionProperty);
            if (empty($newCartPositionProperty) || empty($newCartPositionProperty->kWarenkorbPosEigenschaft)) {
                $this->debugLog('Create Order for Subscription: Failed to insert cart position property into database. Old ID: ' . $cartPositionProperty->kWarenkorbPosEigenschaft, __CLASS__);
                $this->rollbackOrderCreation($rollbackData);
                throw new Exception('Create Order for Subscription: Failed to insert cart position property into database. Old ID: ' . $cartPositionProperty->kWarenkorbPosEigenschaft, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
            }
            $rollbackData['cartPositionPropertyIds'][] = (int)$newCartPositionProperty->kWarenkorbPosEigenschaft;
        }

        // insert new billing address
        $newBillingAddress = $this->duplicateBillingAddress($billingAddress);
        $newBillingAddress = $this->database->insertNewBillingAddress($newBillingAddress);
        if (empty($newBillingAddress) || empty($newBillingAddress->kRechnungsadresse)) {
            $this->debugLog('Create Order for Subscription: Failed to insert billing address into database. Old ID: ' . $billingAddress->kRechnungsadresse, __CLASS__);
            $this->rollbackOrderCreation($rollbackData);
            throw new Exception('Create Order for Subscription: Failed to insert billing address into database. Old ID: ' . $billingAddress->kRechnungsadresse, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }
        $rollbackData['invoiceAddressId'] = (int)$newBillingAddress->kRechnungsadresse;

        // insert new order
        $newOrder = $this->duplicateOrder($order, (int)$newBillingAddress->kRechnungsadresse, (int)$newCart->kWarenkorb);

        // Override payment method ID if needed (this can happen on re-installation or updates of the plugin)
        $paymentMethod = $this->database->getPaymentMethodByModuleId(AmazonPay::getModuleId($this->plugin));
        if ((int)$paymentMethod->kZahlungsart !== (int)$newOrder->kZahlungsart) {
            $this->debugLog('Create Order for Subscription: Payment method id kZahlungsart has changed. Overriding with current payment method id.');
            $newOrder->kZahlungsart = $paymentMethod->kZahlungsart;
        }

        // insert new order into the database
        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedClassInspection - Class exists only in 5.2.0+*/
            $orderHandler = new OrderHandler(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
            $newOrder->cBestellNr = $orderHandler->createOrderNo();
        } else {
            /** @noinspection PhpDeprecationInspection */
            $newOrder->cBestellNr = baueBestellnummer();
        }
        $newOrder = $this->database->insertNewOrder($newOrder);

        if (empty($newOrder) || empty($newOrder->kBestellung)) {
            // we could not insert the order into the database - that's bad.
            $this->debugLog('Create Order for Subscription: Failed to insert order into the database.', __CLASS__);
            $this->rollbackOrderCreation($rollbackData);
            throw new Exception('Create Order for Subscription: Failed to insert order into the database.' . $chargeState, Constants::SUBSCRIPTION_EXCEPTION_CODE_NOT_RECOVERABLE);
        }

        /*
         * After this point the order is already in the db and a full order, so we do not rollback from here on.
         */

        // Copy order attributes - note that our own attributes may need to be updated!
        $orderAttributes = $this->database->getShopOrderAttributes((int)$order->kBestellung);
        foreach ($orderAttributes as $orderAttribute) {
            $newOrderAttribute = $this->duplicateOrderAttribute($orderAttribute, (int)$newOrder->kBestellung);
            if ($newOrderAttribute->cName === $this->config->getSubscriptionOrderAttributeFlag()) {
                $newOrderAttribute->cValue = $order->cBestellNr; // for recurring orders we set the original order number in this field
            }
            $this->database->insertNewOrderAttribute($newOrderAttribute);
        }
        $this->debugLog('Create Order for Subscription: Order was placed with the shop.', __CLASS__);
        $newOrderObject = new Bestellung((int)$newOrder->kBestellung, true);

        // Add mapping for subscription to the new order
        $this->database->addSubscriptionOrder($subscriptionId, (int)$newOrderObject->kBestellung, (int)$order->kBestellung);

        // update the charge object with the actual order id - this is needed to properly handle captures / setting the incoming payments
        $charge->setShopOrderId((int)$newOrderObject->kBestellung);
        $charge->setChargePermissionId($chargePermission->getChargePermissionId()); // we need to set this manually as it is not part of the actual amazon pay charge
        $this->database->saveCharge($charge);

        // log order
        $logger = Shop::Container()->getLogService();
        if ($logger->isHandling(JTLLOG_LEVEL_DEBUG)) {
            $logger->withName('kBestellung')->debug('Bestellung gespeichert: ' . print_r($newOrderObject, true), [(int)$newOrder->kBestellung]);
        }

        // Update stock levels
        foreach ($cartProductPositions as $cartProductPosition) {
            if(Compatibility::isShopAtLeast52()) {
                /** @noinspection PhpUndefinedClassInspection - Class exists only in 5.2.0+*/
                $stockUpdater = new StockUpdater(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
                $stockUpdater->updateStock($cartProductPosition->Artikel, (float)$cartProductPosition->nAnzahl, array_filter($cartPositionProperties, static function ($property) use ($cartProductPosition) {
                    return (int)$property->kWarenkorbPos === (int)$cartProductPosition->kWarenkorbPos;
                }));
            } else {
                /** @noinspection PhpDeprecationInspection */
                aktualisiereLagerbestand($cartProductPosition->Artikel, (float)$cartProductPosition->nAnzahl, array_filter($cartPositionProperties, static function ($property) use ($cartProductPosition) {
                    return (int)$property->kWarenkorbPos === (int)$cartProductPosition->kWarenkorbPos;
                }));
            }
            Shop::Container()->getCache()->flushTags([CACHING_GROUP_ARTICLE . '_' . $cartProductPosition->kArtikel]);
        }
        $this->sendOrderConfirmationMail(new Customer((int)$newOrder->kKunde), $newOrderObject, $availabilityArray);

        // Order is now basically complete, let's see if we should also capture it immediately
        $captureMode = $this->config->getCaptureMode();

        // handle immediate capture
        if ($captureMode === Config::CAPTURE_MODE_IMMEDIATE && $charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZED) {
            // The charge is authorized and we are in immediate capture mode, we cap it now
            $captureChargeRequest = new CaptureCharge($charge->getChargeId(), $charge->getChargeAmount());
            try {
                $response = $apiAdapter->execute($captureChargeRequest);
                if ($response instanceof Error) {
                    // an error - ok, but we can live with that for now.
                    $this->debugLog('Create Order for Subscription: Failed to capture charge during order completion with Error: ' . $response->getReasonCode(), __CLASS__);
                    $this->noticeLog('Create Order for Subscription: Immediate capture for charge ' . $charge->getChargeId() . ' (Charge Permission ' . $chargePermission->getChargePermissionId() . ') has failed. Please observe this order carefully!', __CLASS__);
                } else {
                    // update our known charge with the newest result
                    /** @var Charge $response */
                    $charge = $response;
                    // also set our internal references to the new object
                    $charge->setShopOrderId((int)$newOrderObject->kBestellung);
                    $charge->setChargePermissionId($chargePermission->getChargePermissionId());
                    $this->database->saveCharge($charge);
                }
            } catch (Exception $exception) {
                // an exception - ok, but we can live with that for now.
                $this->debugLog('Failed to capture charge during order completion with Exception: ' . $exception->getMessage(), __CLASS__);
            }
        }

        /*
         * now the charge may be in one of the following states:
         * Authorized (unchanged from before, it will be capped later),
         * CaptureInitiated (for immediate capture only. but unlikely at this point in time, because it is not older than 7 days, but it would resolve later),
         * Captured (for immediate capture only. successfully immediately captured) or
         * Declined (for immediate capture only. the immediate capture failed)
         */
        if ($charge->getStatusDetails()->getState() === StatusDetails::STATUS_CAPTURED) {
            // this is an incoming payment - set it of we should do this.
            if ($this->config->isAddIncomingPayments()) {
                $captureAmount = $charge->getCaptureAmount();
                if ($captureAmount !== null) {
                    $paymentMethodModule->addIncomingPayment($newOrderObject, (object)[
                        'fBetrag' => (float)$captureAmount->getAmount(),
                        'cISO' => $captureAmount->getCurrencyCode(),
                        'cHinweis' => $charge->getChargeId()
                    ]);
                    // A capture that is completed at this point implies a complete payment of the order
                    $paymentMethodModule->setOrderStatusToPaid($newOrderObject);
                    $paymentMethodModule->sendConfirmationMail($newOrderObject);
                } else {
                    $paymentMethodModule->doLog('Immediate capture for charge ' . $charge->getChargeId() . ' (Charge Permission ' . $chargePermission->getChargePermissionId() . ') has no capture amount. Please handle manually!', LOGLEVEL_ERROR);
                }
            }
        } elseif ($charge->getStatusDetails()->getState() === StatusDetails::STATUS_DECLINED) {
            // Log this to the payment module error log
            $paymentMethodModule->doLog('Immediate capture for charge ' . $charge->getChargeId() . ' (Charge Permission ' . $chargePermission->getChargePermissionId() . ') was declined. Please handle manually!', LOGLEVEL_ERROR);
        }

        // send updated order id to amazon, failure here is not critical - this only updates the order number in the charge permission
        try {
            $merchantMetadata = new MerchantMetadata();
            $merchantMetadata->setMerchantReferenceId(self::getMerchantReferenceIdPrefix() . $newOrderObject->cBestellNr . self::getMerchantReferenceIdSuffix());
            $request = new UpdateChargePermission($chargePermission->getChargePermissionId(), $merchantMetadata);
            $response = $this->adapter->execute($request);
            if($response instanceof Error) {
                // unlucky, but no problem
                /** @var Error $response */
                throw new Exception('Failed to update merchant meta data on charge permission while creating recurring order: ' . $response->getReasonCode());
            }
        } catch (Exception $ex) {
            // log exceptions but this is no fatal problem (we simply did not set optional information in Amazon Pay)
            $this->noticeLog('Exception while trying to set merchant meta data (order number) for charge permission id "' . $chargePermission->getChargePermissionId() . '": ' . $ex->getMessage() . "\n" . $ex->getTraceAsString(), __CLASS__);
        }

        Dispatcher::getInstance()->fire(Events::AFTER_SUBSCRIPTION_RECURRING_ORDER_CREATED, ['subscriptionId' => $subscriptionId, 'subscription' => $subscription, 'order' => $newOrderObject]);

        return $newOrderObject;
    }

    protected function normalizeTimestampTime(int $timestamp, string $timeOfDay): int {
        if (empty($timeOfDay)) {
            $this->debugLog('Cannot normalize time as no time of day is given.', __CLASS__);
            return $timestamp;
        }
        $parts = explode(':', $timeOfDay);
        if (count($parts) !== 2) {
            $this->debugLog('Cannot normalize time as format seems to be wrong.', __CLASS__);
            return $timestamp;
        }
        $hour = (int)ltrim(trim($parts[0]), '0');
        $minute = (int)ltrim(trim($parts[1]), '0');
        $targetDatetime = new DateTime();
        $targetDatetime->setTimestamp($timestamp);
        $targetDatetime->setTime($hour, $minute);
        return $targetDatetime->getTimestamp();
    }

    /**
     * This duplicates an order for the database.
     *
     * Attention: It does NOT set a new order number as this should be done right before inserting the order into the database!
     *
     * @param $order
     * @param int $newBillingAddressId
     * @param int $newCartId
     * @return mixed
     */
    protected function duplicateOrder($order, int $newBillingAddressId, int $newCartId) {
        // Change order number
        $newOrder = clone $order;
        // remove the db id
        unset($newOrder->kBestellung);
        // for safety reasons, lets also unset the order number, as stated above, it MUST be set again before adding this order to the database.
        /** @noinspection UnsetConstructsCanBeMergedInspection */
        unset($newOrder->cBestellNr);
        $newOrder->kWarenkorb = $newCartId;
        $newOrder->kRechnungsadresse = $newBillingAddressId;
        // set cSession empty
        $newOrder->cSession = '';
        // unset shipping data and payment date
        $newOrder->cVersandInfo = null;
        $newOrder->dVersandDatum = null;
        $newOrder->dBezahltDatum = null;
        $newOrder->cTracking = null;
        $newOrder->cLogistiker = '';
        $newOrder->cTrackingURL = '';
        // set IP
        $newOrder->cIP = Request::getRealIP(); // this is most likely the IP of the wawi when we are creating a new subscription on sync
        // make wawi collect the order
        $newOrder->cAbgeholt = 'N';
        // set initial order status
        $newOrder->cStatus = BESTELLUNG_STATUS_OFFEN;
        // set order creation time to the moment the order is written to the database
        $newOrder->dErstellt = 'NOW()';
        // unset PUI data (this may be a special paypal field?)
        $newOrder->cPUIZahlungsdaten = null;
        return $newOrder;
    }

    protected function duplicateCart($cart) {
        $result = clone $cart;
        unset($result->kWarenkorb);
        return $result;
    }

    protected function duplicateBillingAddress($billingAddress) {
        // duplicating a billing address is just removing the kRechnungsadresse, these are shallow objects anyway.
        $result = clone $billingAddress;
        unset($result->kRechnungsadresse);
        return $result;
    }

    protected function duplicateCartPosition($cartPosition, int $newCartId) {
        $newCartPosition = clone $cartPosition;
        unset($newCartPosition->kWarenkorbPos);
        $newCartPosition->kWarenkorb = $newCartId;
        return $newCartPosition;
    }

    protected function duplicateCartPositionProperty($cartPositionProperty, int $newCartPositionId) {
        $newCartPositionProperty = clone $cartPositionProperty;
        unset($newCartPositionProperty->kWarenkorbPosEigenschaft);
        $newCartPositionProperty->kWarenkorbPos = $newCartPositionId;
        return $newCartPositionProperty;
    }

    protected function duplicateOrderAttribute($orderAttribute, int $newOrderId) {
        $newOrderAttribute = clone $orderAttribute;
        unset($newOrderAttribute->kBestellattribut);
        $newOrderAttribute->kBestellung = $newOrderId;
        return $newOrderAttribute;
    }

    protected function sendOrderConfirmationMail(Customer $customer, Bestellung $order, $availabilityArray): void {
        $obj = new \stdClass();
        $obj->cVerfuegbarkeit_arr = $availabilityArray;
        // This replicates the mailing from finalisiereBestellung:
        $obj->tkunde = $customer;
        $obj->tbestellung = $order;
        if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $obj->tbestellung->cEstimatedDeliveryEx = Date::dateAddWeekday(
                    $order->dErstellt,
                    $order->oEstimatedDelivery->longestMin
                )->format('d.m.Y') . ' - ' .
                Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y');
        }
        $mailer = Shop::Container()->get(Mailer::class);
        $mail = new Mail();
        $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj));
    }

    /**
     * Basically the same as pruefeVerfuegbarkeit, but without using the session. This only generates hint texts for the mail.
     * @return array|array[]
     */
    protected function checkAvailability($cartProductPositions): array {
        $res = ['cArtikelName_arr' => []];
        $conf = Shop::getSettings([CONF_GLOBAL]);
        foreach ($cartProductPositions as $item) {
            if ($item->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                && isset($item->Artikel->cLagerBeachten)
                && $item->Artikel->cLagerBeachten === 'Y'
                && $item->Artikel->cLagerKleinerNull === 'Y'
                && $conf['global']['global_lieferverzoegerung_anzeigen'] === 'Y'
                && $item->nAnzahl > $item->Artikel->fLagerbestand
            ) {
                $res['cArtikelName_arr'][] = $item->Artikel->cName;
            }
        }

        if (count($res['cArtikelName_arr']) > 0) {
            $res['cHinweis'] = str_replace('%s', '', Shop::Lang()->get('orderExpandInventory', 'basket'));
        }

        return $res;
    }

    /**
     * Returns false if any of the product positions has run out of stock.
     * @param $cartProductPositions
     * @return bool
     */
    public function checkStockLevels($cartProductPositions): bool {
        $result = true;
        $depAmount = $this->getAllDependentAmount($cartProductPositions);

        foreach ($cartProductPositions as $i => $item) {
            if ($item->kArtikel <= 0
                || $item->Artikel->cLagerBeachten !== 'Y'
                || $item->Artikel->cLagerKleinerNull === 'Y'
            ) {
                continue;
            }

            // Lagerbestand beachten und keine Überverkäufe möglich
            if (isset($item->WarenkorbPosEigenschaftArr)
                && !$item->Artikel->kVaterArtikel
                && !$item->Artikel->nIstVater
                && $item->Artikel->cLagerVariation === 'Y'
                && count($item->WarenkorbPosEigenschaftArr) > 0
            ) {
                // Position mit Variationen, Lagerbestand in Variationen wird beachtet
                foreach ($item->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->kEigenschaftWert > 0 && $item->nAnzahl > 0) {
                        //schaue in DB, ob Lagerbestand ausreichend
                        $stock = Shop::Container()->getDB()->getSingleObject(
                            'SELECT kEigenschaftWert, fLagerbestand >= ' . $item->nAnzahl .
                            ' AS bAusreichend, fLagerbestand
                                FROM teigenschaftwert
                                WHERE kEigenschaftWert = ' . (int)$oWarenkorbPosEigenschaft->kEigenschaftWert
                        );
                        if ($stock !== null && $stock->kEigenschaftWert > 0 && !$stock->bAusreichend) {
                            // at least one product without enough stock
                            $result = false;
                            break;
                        }
                    }
                }
            } else {
                // Position ohne Variationen bzw. Variationen ohne eigenen Lagerbestand
                // schaue in DB, ob Lagerbestand ausreichend
                // check depended products
                $depProducts = $item->Artikel->getAllDependentProducts(true); // Note that this also includes the article itself
                $depStock = Shop::Container()->getDB()->getObjects(
                    'SELECT kArtikel, fLagerbestand
                        FROM tartikel
                        WHERE kArtikel IN (' . implode(', ', array_keys($depProducts)) . ')'
                );
                foreach ($depStock as $productStock) {
                    $productID = (int)$productStock->kArtikel;

                    if ($depProducts[$productID]->product->fPackeinheit * $depAmount[$productID]
                        > $productStock->fLagerbestand
                    ) {
                        $result = false;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    protected function getAllDependentAmount($cartProductPositions): array {
        $depAmount = [];
        foreach ($cartProductPositions as $cartItem) {
            if (!empty($cartItem->Artikel)
                && ($cartItem->Artikel->cLagerBeachten === 'Y' && $cartItem->Artikel->cLagerKleinerNull !== 'Y')
            ) {
                $depProducts = $cartItem->Artikel->getAllDependentProducts(true);

                foreach ($depProducts as $productID => $item) {
                    if (isset($depAmount[$productID])) {
                        $depAmount[$productID] += ($cartItem->nAnzahl * $item->stockFactor);
                    } else {
                        $depAmount[$productID] = $cartItem->nAnzahl * $item->stockFactor;
                    }
                }
            }
        }
        return $depAmount;
    }

    /**
     * Returns false if any of the product positions should be deactivated (i.e. cant be bought)
     * @param $cartProductPositions
     * @return bool
     */
    protected function checkDeactivatedPositions($cartProductPositions): bool {
        $result = true;
        foreach ($cartProductPositions as $item) {
            $item->nPosTyp = (int)$item->nPosTyp;
            if (!empty($item->Artikel)) {
                if (empty($item->kKonfigitem)
                    && (float)$item->fPreisEinzelNetto === (float)0
                    && !$item->Artikel->bHasKonfig
                    && $item->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK
                    && isset($item->fPreisEinzelNetto, $this->config['global']['global_preis0'])
                    && $this->config['global']['global_preis0'] === 'N'
                ) {
                    $result = false;
                } elseif (!empty($item->Artikel->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                    $result = false;
                } else {
                    $result = !(Shop::Container()->getDB()->select(
                            'tartikel',
                            'kArtikel',
                            $item->kArtikel
                        ) === null);
                }
            }
        }
        return $result;
    }

    protected function rollbackOrderCreation(array $rollbackData): void {
        /**
         * $rollbackData has the following optional contents:
         * - chargeId
         * - cartId
         * - cartPositionIds
         * - cartPositionPropertyIds
         * - invoiceAddressId
         *
         * NOTE: We delete the elements in reverse order on how they are created to not have inconsistencies in the database at any point!
         */
        if (!empty($rollbackData['invoiceAddressId'])) {
            $this->database->deleteInvoiceAddress((int)$rollbackData['invoiceAddressId']);
        }
        if (!empty($rollbackData['cartPositionPropertyIds'])) {
            foreach ($rollbackData['cartPositionPropertyIds'] as $cartPositionPropertyId) {
                if (!empty($cartPositionPropertyId)) {
                    $this->database->deleteCartPositionProperty((int)$cartPositionPropertyId);
                }
            }
        }
        if (!empty($rollbackData['cartPositionIds'])) {
            foreach ($rollbackData['cartPositionIds'] as $cartPositionId) {
                if (!empty($cartPositionId)) {
                    $this->database->deleteCartPosition((int)$cartPositionId);
                }
            }
        }
        if (!empty($rollbackData['cartId'])) {
            $this->database->deleteCart((int)$rollbackData['cartId']);
        }
        if (!empty($rollbackData['chargeId'])) {
            $this->database->deleteCharge($rollbackData['chargeId']);
        }
    }

    public function cancelSubscriptionForChargePermission(string $chargePermissionId, $reason = ''): void {
        $subscriptions = $this->database->getSubscriptionsByChargePermissionId($chargePermissionId);
        if (!empty($subscriptions)) {
            /** @var Subscription $subscription */
            foreach ($subscriptions as $subscription) {
                $this->debugLog('Canceling subscription ' . $subscription->getID() . ' with reason "' . $reason . '"', __CLASS__);
                try {
                    $this->cancelSubscription($subscription->getId(), $reason);
                } catch (Exception $e) {
                    // Failing here is not a problem, the subscription will still automatically cancel itself before renewal if the charge permission does not allow it to continue.
                    $this->debugLog('Failed to cancel subscription: ' . $e->getMessage());
                    continue;
                }
            }
        }
    }

    public function getSubscriptionsForCustomer(int $customerId) {
        $subscriptions = $this->database->getSubscriptionsByCustomerId($customerId);
        if(empty($subscriptions)) {
            return [];
        }
        return $subscriptions;
    }

    /**
     * @throws Exception
     */
    public function setSubscriptionToReview(?int $subscriptionId, $reason = ''): ?Subscription {
        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        if($subscription->getStatus() !== Subscription::STATUS_PAUSED) {
            $subscription->setStatus(Subscription::STATUS_PAUSED);
            $subscription->setStatusReason($reason);
            $result = $this->database->upsertSubscription($subscription);
            Dispatcher::getInstance()->fire(Events::AFTER_SUBSCRIPTION_IN_REVIEW, ['subscriptionId' => $subscriptionId, 'subscription' => $subscription, 'reason' => $reason]);
            return $result;
        }
        return $subscription;
    }

    /**
     * @throws Exception
     */
    public function setSubscriptionToActive(int $subscriptionId, $reason = ''): ?Subscription {
        $subscription = $this->database->selectSubscriptionById($subscriptionId);
        if ($subscription === null) {
            $this->debugLog('Failed to load subscription by id for id ' . $subscriptionId, __CLASS__);
            throw new Exception('Failed to load subscription by id for id ' . $subscriptionId, Constants::SUBSCRIPTION_EXCEPTION_CODE_GENERIC);
        }
        if($subscription->getStatus() !== Subscription::STATUS_ACTIVE) {
            $subscription->setStatus(Subscription::STATUS_ACTIVE);
            $subscription->setStatusReason($reason);
            $result = $this->database->upsertSubscription($subscription);
            Dispatcher::getInstance()->fire(Events::AFTER_SUBSCRIPTION_ACTIVE, ['subscriptionId' => $subscriptionId, 'subscription' => $subscription, 'reason' => $reason]);
            return $result;
        }
        return $subscription;
    }

    public static function getMerchantReferenceIdPrefix() {
        return '';
    }

    public static function getMerchantReferenceIdSuffix() {
        return ' - ' . date('Y-m-d');
    }

}