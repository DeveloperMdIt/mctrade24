<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyAttributesTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBillingAdressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyCardTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyStoredCredentialTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyTypeTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyVaultIdTrait;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class CardPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class CardPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyCardTrait;
    use PropertyBillingAdressTrait;
    use PropertyStoredCredentialTrait;
    use PropertyVaultIdTrait;
    use PropertyExperienceContextTrait;
    use PropertyAttributesTrait;
    use PropertyTypeTrait;

    /**
     * CardPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'name',
            'number',
            'security_code',
            'expiry',
            'type',
            'billing_address',
            'attributes',
            'stored_credential',
            'vault_id',
            'experience_context',
            'attribute_vault',
        ]);
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        return new ExperienceContext($data, [
            'return_url',
            'cancel_url',
        ]);
    }

    protected function createVaultRequest(): void
    {
        $this->addAttribute('vault', new JSON((object)[
            'store_in_vault' => 'ON_SUCCESS',
        ]));
        $this->setStoredCredential((new StoredCredential())
            ->setPaymentInitiator(StoredCredential::PI_CUSTOMER)
            ->setPaymentType(StoredCredential::PTYPE_ONE_TIME)
            ->setUsage(StoredCredential::USAGE_FIRST));
    }

    protected function removeVaultRequest(): void
    {
        $this->addAttribute('vault');
        $this->setStoredCredential();
    }
}
