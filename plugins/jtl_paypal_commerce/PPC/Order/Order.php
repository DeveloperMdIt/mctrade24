<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

use function Functional\first;

/**
 * Class Order
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Order extends JSON
{
    public const INTENT_CAPTURE   = 'CAPTURE';
    public const INTENT_AUTHORIZE = 'AUTHORIZE';
    public const PI_AUTO_COMPLETE = 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL';

    /** @var string|null */
    protected ?string $id = null;

    /** @var string|null */
    protected ?string $status = null;

    /** @var int|null */
    protected ?int $orderId = null;

    /** @var object[] */
    protected array $links = [];

    /** @var string */
    private string $customProcessMessage = '';

    /**
     * Order constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'intent'         => self::INTENT_AUTHORIZE,
            'purchase_units' => [],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $purchases = $this->getData()->purchase_units ?? [];
        foreach (\array_keys($purchases) as $key) {
            if (!($purchases[$key] instanceof PurchaseUnit)) {
                $purchases[$key] = (new PurchaseUnit($purchases[$key]));
            }
        }
        $this->setPurchases($purchases);

        $id = $this->getData()->id ?? null;
        if ($id !== null) {
            $this->setId($id);
            unset($this->data->id);
        }
        $status = $this->getData()->status ?? null;
        if ($status !== null) {
            $this->setStatus($status);
            unset($this->data->status);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?? '';
    }

    /**
     * @param string $id
     * @return Order
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntent(): string
    {
        return $this->data->intent ?? self::INTENT_AUTHORIZE;
    }

    /**
     * @param string $intent
     * @return Order
     */
    public function setIntent(string $intent): self
    {
        if (!\in_array($intent, [self::INTENT_AUTHORIZE, self::INTENT_CAPTURE])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid intent.', $intent));
        }

        $this->data->intent = $intent;

        return $this;
    }

    /**
     * @return Payer|null
     */
    public function getPayer(): ?Payer
    {
        $paymentSource = $this->getPaymentSource();
        $payer         = $this->getData()->payer ?? ($paymentSource === null ? null : $paymentSource->fetchPayer());
        if ($payer === null) {
            return null;
        }

        return $payer instanceof Payer ? $payer : new Payer($payer);
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status ?? OrderStatus::STATUS_UNKONWN;
    }

    /**
     * @param string $status
     * @return Order
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array
     */
    public function getPurchases(): array
    {
        return $this->data->purchase_units;
    }

    /**
     * @param string $referenceId
     * @return PurchaseUnit
     */
    public function getPurchase(string $referenceId = PurchaseUnit::REFERENCE_DEFAULT): PurchaseUnit
    {
        $purchase = first($this->data->purchase_units, static function (PurchaseUnit $item) use ($referenceId) {
            return $item->getReferenceId() === $referenceId;
        });
        if ($purchase === null) {
            $purchase = (new PurchaseUnit())->setReferenceId($referenceId);
            $this->addPurchase($purchase);
        }

        return $purchase;
    }

    /**
     * @param PurchaseUnit[] $purchases
     * @return Order
     */
    public function setPurchases(array $purchases): self
    {
        $this->data->purchase_units = $purchases;

        return $this;
    }

    /**
     * @param PurchaseUnit $purchase
     * @return Order
     */
    public function addPurchase(PurchaseUnit $purchase): self
    {
        $this->data->purchase_units[] = $purchase;

        return $this;
    }

    /**
     * @param string $referenceId
     * @return Order
     */
    public function removePurchase(string $referenceId): self
    {
        $this->data->purchase_units = \array_filter(
            $this->data->purchase_units,
            static function (PurchaseUnit $item) use ($referenceId) {
                return $item->getReferenceId() !== $referenceId;
            }
        );

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId ?? 0;
    }

    /**
     * @param int $orderId
     * @return Order
     */
    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomId(): string
    {
        $customId = $this->data->custom_id ?? '';
        if ($customId === '') {
            $customId = $this->getPurchase()->getCustomId() ?? '';
        }

        return $customId;
    }

    /**
     * @return string
     */
    public function getInvoiceId(): string
    {
        $invoiceId = $this->data->invoice_id ?? '';
        if ($invoiceId === '') {
            $invoiceId = $this->getPurchase()->getInvoiceId() ?? '';
        }

        return $invoiceId;
    }

    /**
     * @return string[]
     */
    public function getPaymentSources(): array
    {
        $paymentSource = $this->getData()->payment_source ?? null;
        if (!\is_object($paymentSource)) {
            return [];
        }

        $sources = [];
        foreach ($paymentSource as $sourceName => $source) {
            $sources[] = $sourceName;
        }

        return $sources;
    }

    /**
     * @param string|null $name
     * @return PaymentSourceInterface|null
     */
    public function getPaymentSource(?string $name = null): ?PaymentSourceInterface
    {
        if ($name === null) {
            $name = $this->getPaymentSources()[0] ?? '';
        }
        $paymentSource = $this->getData()->payment_source ?? null;
        if ($paymentSource !== null && !empty($name) && isset($paymentSource->$name)) {
            return (new PaymentSourceBuilder($name))
                ->setData($paymentSource->$name)
                ->build();
        }

        return null;
    }

    /**
     * @param string        $name
     * @param PaymentSourceInterface $paymentSource
     * @return Order
     */
    public function setPaymentSource(string $name, PaymentSourceInterface $paymentSource): self
    {
        $sources        = $this->data->payment_source ?? (object)[];
        $sources->$name = $paymentSource;

        $this->data->payment_source = $sources;

        return $this;
    }

    /**
     * @return string
     */
    public function getProcessingInstruction(): string
    {
        return $this->data->processing_instruction ?? '';
    }

    /**
     * @param string $processingInstruction
     * @return Order
     */
    public function setProcessingInstruction(string $processingInstruction): self
    {
        $this->data->processing_instruction = $processingInstruction;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomProcessMessage(): string
    {
        return $this->customProcessMessage;
    }

    /**
     * @param string $customProcessMessage
     * @return Order
     */
    public function setCustomProcessMessage(string $customProcessMessage): self
    {
        $this->customProcessMessage = $customProcessMessage;

        return $this;
    }

    /**
     * @param object $link
     * @return Order
     */
    public function setLink(object $link): self
    {
        $links = $this->data->links;
        $rel   = $link->rel;
        $key   = first(\array_keys($links), static function (int $key) use ($rel, $links) {
            return $links[$key]->rel === $rel;
        });
        if ($key !== null) {
            $this->data->links[$key] = $link;
        } else {
            $this->data->links[] = $link;
        }

        return $this;
    }

    /**
     * @param string $rel
     * @return Order
     */
    public function unsetLink(string $rel): self
    {
        $links = $this->data->links;
        $key   = first(\array_keys($links), static function (int $key) use ($rel, $links) {
            return $links[$key]->rel === $rel;
        });
        if ($key !== null) {
            unset($this->links[$key]);
        }

        return $this;
    }

    /**
     * @param string $rel
     * @return string|null
     */
    public function getLink(string $rel): ?string
    {
        $link = first($this->data->links, static function (object $item) use ($rel) {
            return $item->rel === $rel;
        });

        return $link !== null ? $link->href : null;
    }

    /**
     * @param string $rel
     * @return object|null
     */
    public function getLinkObject(string $rel): ?object
    {
        return first($this->getData()->links, static function (object $item) use ($rel) {
            return $item->rel === $rel;
        });
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->payer) || ($data->payer instanceof SerializerInterface && $data->payer->isEmpty())) {
            unset($data->payer);
        }
        if (
            empty($data->payment_source)
            || ($data->payment_source instanceof SerializerInterface && $data->payment_source->isEmpty())
        ) {
            unset($data->payment_source);
        }
        if (empty($data->processing_instruction)) {
            unset($data->processing_instruction);
        }
        if (empty($data->custom_id)) {
            unset($data->custom_id);
        }
        if (empty($data->invoice_id)) {
            unset($data->invoice_id);
        }

        return $data;
    }
}
