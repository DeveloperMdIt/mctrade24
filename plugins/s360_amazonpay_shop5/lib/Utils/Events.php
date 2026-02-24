<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

/**
 * Class Events
 *
 * Listing of custom events fired by the plugin.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Events {
    private const PREFIX = 'amazonpay.hook.';
    // Fired when loading possible shipping methods for Amazon Pay without knowing the concrete delivery address.
    // Parameters: "shippingMethods" -> Array of shipping method stdClass objects
    public const AVAILABLE_SHIPPING_METHODS = self::PREFIX . 'availableShippingMethods';

    // Fired after determining the possible shipping methods for the country and zip code. These also include prices! (as returned by ShippingMethod::getPossibleShippingMethods())
    // Parameters: "shippingMethods" -> Array of shipping method stdClass objects
    // Parameters: "deliveryAddress" -> Delivery address (class Lieferadresse from JTL) of the customer
    public const AFTER_GET_POSSIBLE_SHIPPING_METHODS = self::PREFIX . 'afterGetPossibleShippingMethods';

    // Fired after a subscription was canceled
    // Parameters: "subscriptionId" -> ID (int) of the cancelled subscription
    // Parameters: "reason" -> Reason (string) for the cancellation
    public const AFTER_SUBSCRIPTION_CANCELED = self::PREFIX . 'afterSubscriptionCanceled';

    // Fired after a subscription was created.
    // Parameters: "subscriptionId" -> ID (int) of the new subscription
    public const AFTER_SUBSCRIPTION_CREATED = self::PREFIX . 'afterSubscriptionCreated';

    // Fired after a subscription ORDER was created (i.e. a recurring order!)
    // Parameters: "subscriptionId" -> ID (int) of the new subscription
    // Parameters: "orderId" -> kBestellung (int) of the newly created order
    public const AFTER_SUBSCRIPTION_RECURRING_ORDER_CREATED = self::PREFIX . 'afterSubscriptionRecurringOrderCreated';

    // Fired after a subscription was set to in review
    // Parameters: "subscriptionId" -> ID (int) of the cancelled subscription
    // Parameters: "reason" -> Reason (string) for the cancellation
    public const AFTER_SUBSCRIPTION_IN_REVIEW = self::PREFIX . 'afterSubscriptionInReview';

    // Fired after a subscription was set to active *FROM A PREVIOUSLY NOT ACTIVE STATE*
    // Parameters: "subscriptionId" -> ID (int) of the activated subscription
    // Parameters: "reason" -> Reason (string) for the activation
    public const AFTER_SUBSCRIPTION_ACTIVE = self::PREFIX . 'afterSubscriptionActive';
}