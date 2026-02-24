<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Vaulting;

use Exception;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceInterface;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

use function Functional\first;

/**
 * Class PaymentToken
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Vaulting
 */
class PaymentToken extends JSON
{
    /**
     * PaymentToken constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    public function getId(): string
    {
        return $this->getData()->id ?? '';
    }

    public function getCustomerId(): string
    {
        return $this->getData()->customer->id ?? '';
    }

    public function getPaymentSource(?string $paymentSourceName = null): ?PaymentSourceInterface
    {
        $paymentSources = $this->getData()->payment_source ?? null;
        if ($paymentSources === null) {
            return null;
        }

        $paymentSourceName ??= first(\array_keys(\get_object_vars($paymentSources)));
        try {
            return (new PaymentSourceBuilder($paymentSourceName))
                ->setData($this->getData()->payment_source->$paymentSourceName ?? null)
                ->build();
        } catch (Exception) {
            return null;
        }
    }
}
