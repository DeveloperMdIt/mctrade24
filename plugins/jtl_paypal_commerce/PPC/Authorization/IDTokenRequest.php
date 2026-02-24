<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;

/**
 * Class IDTokenRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class IDTokenRequest extends TokenRequest
{
    private string $customerVaultId;

    /**
     * IDTokenRequest constructor
     */
    public function __construct(EnvironmentInterface $environment, string $customerVaultId)
    {
        $this->customerVaultId = $customerVaultId;

        parent::__construct($environment);
    }

    protected function initBody(): string
    {
        return \http_build_query(
            [
                'grant_type'         => 'client_credentials',
                'response_type'      => 'id_token',
                'target_customer_id' => $this->customerVaultId,
            ]
        );
    }
}
