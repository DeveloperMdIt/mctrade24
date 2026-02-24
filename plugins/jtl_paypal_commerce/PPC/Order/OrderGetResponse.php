<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceInterface;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OrderGetResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderGetResponse extends OrderCreateResponse
{
    /**
     * OrderGetResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([200]);
    }

    /**
     * @return DateTime|null
     * @throws UnexpectedResponseException
     */
    public function getUpdateTime(): ?DateTime
    {
        try {
            $timeData = $this->getData()->update_time ?? null;

            return $timeData === null ? null : DateTime::createFromFormat(
                DateTimeInterface::RFC3339,
                $this->getData()->update_time
            )->setTimezone(new DateTimeZone(\SHOP_TIMEZONE));
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string[]
     * @throws UnexpectedResponseException
     */
    public function getPaymentSourceNames(): array
    {
        try {
            $paymentSource = $this->getData()->payment_source ?? null;
            if ($paymentSource !== null) {
                return \array_keys(\get_object_vars($paymentSource));
            }
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return [];
    }

    /**
     * @param string $name
     * @return PaymentSourceInterface|null
     * @throws UnexpectedResponseException
     */
    public function getPaymentSource(string $name): ?PaymentSourceInterface
    {
        try {
            $paymentSource = $this->getData()->payment_source ?? null;
            if ($paymentSource !== null && isset($paymentSource->$name)) {
                return (new PaymentSourceBuilder($paymentSource->$name))
                    ->setData($paymentSource->$name)
                    ->build();
            }
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return null;
    }
}
