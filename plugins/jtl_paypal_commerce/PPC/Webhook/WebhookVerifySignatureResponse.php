<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class WebhookVerifySignatureResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookVerifySignatureResponse extends JSONResponse
{
    public const VERIFY_SUCCESS = 'SUCCESS';
    public const VERIFY_FAILURE = 'FAILURE';

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getStatus(): string
    {
        try {
            return $this->getData()->verification_status ?? self::VERIFY_FAILURE;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        try {
            return $this->getStatus() === self::VERIFY_SUCCESS;
        } catch (UnexpectedResponseException) {
            return false;
        }
    }
}
