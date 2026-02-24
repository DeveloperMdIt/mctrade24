<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\BankDetails;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyBankReferenceTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyBankReferenceTrait
{
    public function getPaymentReference(): string
    {
        return $this->getMappedValue('payment_reference') ?? '';
    }

    public function getDepositBankDetails(): ?BankDetails
    {
        $bankDetails = $this->getMappedValue('deposit_bank_details');
        if (empty($bankDetails) || ($bankDetails instanceof SerializerInterface && $bankDetails->isEmpty())) {
            return null;
        }

        return $bankDetails instanceof BankDetails ? $bankDetails : new BankDetails($bankDetails);
    }

    protected function initdataDepositBankDetails(object $data): void
    {
        if (!($data instanceof BankDetails)) {
            $this->data->deposit_bank_details = new BankDetails($data);
        }
    }
}
