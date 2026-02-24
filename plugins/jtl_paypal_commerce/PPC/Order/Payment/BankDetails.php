<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class BankDetails
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class BankDetails extends JSON
{
    /**
     * BankDetails constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'bic'       => '',
            'bank_name' => '',
            'iban'      => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBIC(): string
    {
        return $this->getData()->bic ?? '';
    }

    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->getData()->bank_name ?? '';
    }

    /**
     * @return string
     */
    public function getIBAN(): string
    {
        return $this->getData()->iban ?? '';
    }

    /**
     * @return string
     */
    public function getAccountHolder(): string
    {
        return $this->getData()->account_holder_name ?? '';
    }
}
