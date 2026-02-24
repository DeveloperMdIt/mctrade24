<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

/**
 * Class Status
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderStatus
{
    public const STATUS_UNKONWN = '';

    /**
     * The order was created with the specified context.
     */
    public const STATUS_CREATED = 'CREATED';

    /**
     * The order was saved and persisted. The order status continues to be in progress until a capture is made with
     * final_capture = true for all purchase units within the order.
     */
    public const STATUS_SAVED = 'SAVED';

    /**
     * The customer approved the payment through the PayPal wallet or another form of guest or unbranded payment.
     * For example, a card, bank account, or so on.
     */
    public const STATUS_APPROVED = 'APPROVED';

    /**
     * All purchase units in the order are voided.
     */
    public const STATUS_VOIDED = 'VOIDED';

    /**
     *  The payment was authorized or the authorized payment was captured for the order.
     */
    public const STATUS_COMPLETED = 'COMPLETED';

    /**
     * The order requires an action from the payer (e.g. 3DS authentication). Redirect the payer to the
     * "rel":"payer-action" HATEOAS link returned as part of the response prior to authorizing or capturing the order.
     */
    public const STATUS_PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';

    /**
     * The funds for this captured payment was not yet credited to the payee's PayPal account.
     */
    public const STATUS_PENDING = 'PENDING';

    /**
     * The payment will be approved by external payer - e.g. Ratepay for Pay upon invoice payments
     */
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';

    /**
     * The payment was declined
     */
    public const STATUS_DECLINED = 'DECLINED';

    /**
     * There was an error while capturing payment.
     */
    public const STATUS_FAILED = 'FAILED';
}
