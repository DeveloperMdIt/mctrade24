<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use GuzzleHttp\Psr7\Uri;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequest;

/**
 * Class GetReferralsRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class ReferralsRequest extends PPCRequest
{
    /**
     * GetReferralsRequest constructor.
     * @param string $partnerId
     * @param string $accessToken
     * @param string $endpoint
     */
    public function __construct(string $partnerId, string $accessToken, string $endpoint)
    {
        $uriString = '/v1/customer/partners/' . $partnerId . $endpoint;
        (new Logger(LOGGER::TYPE_ONBOARDING))->write(
            \LOGLEVEL_DEBUG,
            'Referrals Request..',
            $uriString
        );
        parent::__construct(
            new Uri($uriString),
            MethodType::GET,
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ]
        );
    }
}
