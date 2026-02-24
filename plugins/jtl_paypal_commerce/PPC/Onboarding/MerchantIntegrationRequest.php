<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class MerchantIntegrationRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class MerchantIntegrationRequest extends AuthorizedRequest
{
    /** @var string */
    private string $partnerID;

    /** @var string */
    private string $merchantId;

    /**
     * @param string $token
     * @param string $partnerId
     * @param string $merchantId
     */
    public function __construct(string $token, string $partnerId, string $merchantId)
    {
        $this->partnerID  = $partnerId;
        $this->merchantId = $merchantId;

        parent::__construct($token, MethodType::GET);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new Nullable();
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/customer/partners/' . $this->partnerID . '/merchant-integrations/' . $this->merchantId;
    }
}
