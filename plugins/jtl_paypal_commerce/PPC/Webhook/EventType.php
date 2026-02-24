<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class EventType
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class EventType extends JSON
{
    public const AUTH_CREATED = 'PAYMENT.AUTHORIZATION.CREATED';  /* Authorize an order
                                                                  /v2/checkout/orders/{order_id}/authorize */
    public const AUTH_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED';    /* Void an authorization
                                                                  /v2/payments/authorizations/{authorization_id}/void */
    public const ORDER_APPROVED = 'CHECKOUT.ORDER.APPROVED';      /* Buyer approval */

    public const CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED'; /* Capture an order or authorization
                                                                  /v2/checkout/orders/{order_id}/capture */
    public const CAPTURE_DENIED = 'PAYMENT.CAPTURE.DENIED';       /* Capture an order or authorization
                                                                  /v2/checkout/orders/{order_id}/capture' */
    public const CAPTURE_PENDING = 'PAYMENT.CAPTURE.PENDING';     /* Capture an order or authorization
                                                                  /v2/checkout/orders/{order_id}/capture */
    public const CAPTURE_REFUNDED = 'PAYMENT.CAPTURE.REFUNDED';   /* Refund a capture
                                                                  /v2/payments/captures/{capture_id}/refund */
    public const CAPTURE_REVERSED = 'CHECKOUT.PAYMENT-APPROVAL.REVERSED';   /* Failed order capture */

    public const VAULT_TOKEN_DELETED = 'VAULT.PAYMENT-TOKEN.DELETED';

    /**
     * EventType constructor.
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
                'name' => self::AUTH_CREATED,
        ]);
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->data->name = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->data->name ?? self::AUTH_CREATED;
    }
}
