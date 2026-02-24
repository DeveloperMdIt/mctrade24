<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyBillingAgreementIdTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyBillingAgreementIdTrait
{
    public function setBillingAgreementId(string $billingAgreementId): static
    {
        $this->setMappedValue(
            'billing_agreement_id',
            PPCHelper::validateStr($billingAgreementId, 2, 128, '^[a-zA-Z0-9-]+$')
        );

        return $this;
    }

    public function getBillingAgreementId(): string
    {
        return $this->getMappedValue('billing_agreement_id') ?? '';
    }
}
