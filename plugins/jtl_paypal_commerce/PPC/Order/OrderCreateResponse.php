<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

use function Functional\first;

/**
 * Class OrderCreateResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderCreateResponse extends JSONResponse implements OrderFullResponse
{
    /**
     * OrderCreateResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([200, 201]);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        try {
            return $this->getData()->id ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        try {
            return $this->getData()->status;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getCreateTime(): DateTime
    {
        try {
            return  DateTime::createFromFormat(
                DateTimeInterface::RFC3339,
                $this->getData()->create_time
            )->setTimezone(new DateTimeZone(\SHOP_TIMEZONE));
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getIntent(): string
    {
        try {
            return $this->getData()->intent ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPayer(): ?Payer
    {
        try {
            $payerData = $this->getData()->payer ?? null;

            return $payerData === null ? null : new Payer($payerData);
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPurchases(): array
    {
        $result = [];
        try {
            $units = $this->getData()->purchase_units ?? null;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        if ($units === null) {
            return $result;
        }

        foreach ($units as $unit) {
            $result[] = new PurchaseUnit($unit);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPurchase(string $referenceId = PurchaseUnit::REFERENCE_DEFAULT): PurchaseUnit
    {
        $units = $this->getPurchases();
        if ($referenceId === PurchaseUnit::REFERENCE_DEFAULT) {
            return $units[0];
        }

        return first($units, static function (PurchaseUnit $item) use ($referenceId) {
            return $item->getReferenceId() === $referenceId;
        });
    }

    /**
     * @param string $rel
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getLink(string $rel): ?string
    {
        try {
            $link = first($this->getData()->links, static function (object $item) use ($rel) {
                return $item->rel === $rel;
            });
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return $link !== null ? $link->href : null;
    }

    /**
     * @param string $rel
     * @return object|null
     * @throws UnexpectedResponseException
     */
    public function getLinkObject(string $rel): ?object
    {
        try {
            return first($this->getData()->links, static function (object $item) use ($rel) {
                return $item->rel === $rel;
            });
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }
}
