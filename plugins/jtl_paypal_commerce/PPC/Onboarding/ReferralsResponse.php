<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class ReferralsResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class ReferralsResponse extends MerchantIntegrationResponse
{
    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getClientId(): string
    {
        try {
            return $this->getData()->client_id ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected referrals response', $e->getCode(), $e);
        }
    }

    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getClientSecret(): string
    {
        try {
            return $this->getData()->client_secret ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected referrals response', $e->getCode(), $e);
        }
    }

    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getPayerId(): string
    {
        try {
            return $this->getData()->payer_id ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected referrals response', $e->getCode(), $e);
        }
    }

    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getTrackingId(): string
    {
        try {
            return $this->getData()->tracking_id ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected referrals response', $e->getCode(), $e);
        }
    }
}
