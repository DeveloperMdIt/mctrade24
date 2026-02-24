<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class ExperienceContext
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class ExperienceContext extends JSON
{
    /** @var string[] string[] */
    private array $supportedProps;

    public const SHIPPING_FROM_FILE   = 'GET_FROM_FILE';
    public const SHIPPING_NO_SHIPPING = 'NO_SHIPPING';
    public const SHIPPING_PROVIDED    = 'SET_PROVIDED_ADDRESS';

    public const PAGE_LOGIN          = 'LOGIN';
    public const PAGE_GUEST_CHECKOUT = 'GUEST_CHECKOUT';
    public const PAGE_NO_PREFERENCE  = 'NO_PREFERENCE';

    public const USER_ACTION_CONTINUE = 'CONTINUE';
    public const USER_ACTION_PAY_NOW  = 'PAY_NOW';

    public const METHOD_IMMEDIATE_PAYMENT_REQUIRED = 'IMMEDIATE_PAYMENT_REQUIRED';
    public const METHOD_UNRESTRICTED               = 'UNRESTRICTED';

    /**
     * ExperienceContext constructor.
     * @param object|null $data
     * @param string[]    $supportedProps
     */
    public function __construct(?object $data = null, array $supportedProps = [])
    {
        parent::__construct($data ?? (object)[
            'locale' => 'de-DE'
        ]);

        $this->supportedProps = $supportedProps;
    }

    /**
     * @inheritDoc
     */
    public function setData(object|array|string $data): static
    {
        if (\is_array($data->customer_service_instructions ?? '')) {
            $data->customer_service_instructions = $data->customer_service_instructions[0] ?? '';
        }

        return parent::setData($data);
    }


    public function isPropertyActive(string $propertyName): bool
    {
        return \in_array($propertyName, $this->supportedProps, true);
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getData()->locale;
    }

    /**
     * @param string $locale
     * @return self
     */
    public function setLocale(string $locale): self
    {
        $this->data->locale = PPCHelper::validateStr(
            $locale,
            2,
            10,
            '^[a-z]{2}(?:-[A-Z][a-z]{3})?(?:-(?:[A-Z]{2}|[0-9]{3}))?$'
        );

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrandName(): ?string
    {
        return $this->getData()->brand_name ?? null;
    }

    /**
     * @param string|null $brandName
     * @return self
     */
    public function setBrandName(?string $brandName = null): self
    {
        if ($brandName === null) {
            unset($this->data->brand_name);
        } else {
            $this->data->brand_name = PPCHelper::shortenStr($brandName, 127);
        }

        return $this;
    }

    public function getShippingPreference(): string
    {
        return $this->getData()->shipping_preference ?? self::SHIPPING_FROM_FILE;
    }

    public function setShippingPreference(string $shipping = self::SHIPPING_FROM_FILE): self
    {
        if (!\in_array($shipping, [self::SHIPPING_NO_SHIPPING, self::SHIPPING_FROM_FILE, self::SHIPPING_PROVIDED])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid shipping preference', $shipping));
        }

        $this->data->shipping_preference = $shipping;

        return $this;
    }

    public function getLandingPage(): string
    {
        return $this->getData()->landing_page ?? self::PAGE_NO_PREFERENCE;
    }

    public function setLandingPage(string $landingPage = self::PAGE_NO_PREFERENCE): self
    {
        if (!\in_array($landingPage, [self::PAGE_LOGIN, self::PAGE_GUEST_CHECKOUT, self::PAGE_NO_PREFERENCE])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid landing page', $landingPage));
        }

        $this->data->landing_page = $landingPage;

        return $this;
    }

    public function getUserAction(): string
    {
        return $this->getData()->user_action ?? self::USER_ACTION_CONTINUE;
    }

    public function setUserAction(string $payAction = self::USER_ACTION_CONTINUE): self
    {
        if (!\in_array($payAction, [self::USER_ACTION_PAY_NOW, self::USER_ACTION_CONTINUE])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid user action', $payAction));
        }

        $this->data->user_action = $payAction;

        return $this;
    }

    public function getPaymentMethodPreference(): string
    {
        return $this->getData()->payment_method_preference ?? self::METHOD_UNRESTRICTED;
    }

    public function setPaymentMethodPreference(string $paymentMethodPreference = self::METHOD_UNRESTRICTED): self
    {
        if (
            !\in_array($paymentMethodPreference, [
                self::METHOD_IMMEDIATE_PAYMENT_REQUIRED,
                self::METHOD_UNRESTRICTED,
            ])
        ) {
            throw new InvalidArgumentException(
                \sprintf('%s is not a valid payment method preference', $paymentMethodPreference)
            );
        }

        $this->data->payment_method_preference = $paymentMethodPreference;

        return $this;
    }

    public function getReturnUrl(): string
    {
        return $this->getData()->return_url ?? '';
    }

    public function setReturnURL(?string $returnURL = null): self
    {
        if ($returnURL === null) {
            unset($this->data->return_url);
        } else {
            $this->data->return_url = $returnURL;
        }

        return $this;
    }

    public function getCancelUrl(): string
    {
        return $this->getData()->cancel_url ?? '';
    }

    public function setCancelURL(?string $cancelURL = null): self
    {
        if ($cancelURL === null) {
            unset($this->data->cancel_url);
        } else {
            $this->data->cancel_url = $cancelURL;
        }

        return $this;
    }

    public function getCustomerServiceInstruction(): string
    {
        return $this->getData()->customer_service_instructions ?? '';
    }

    public function setCustomerServiceInstruction(string $instruction): self
    {
        $this->data->customer_service_instructions = $instruction;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        $isEmpty = parent::isEmpty();
        if ($isEmpty || empty($this->supportedProps)) {
            return $isEmpty;
        }

        foreach ($this->data as $propName => $propValue) {
            if (!\in_array($propName, $this->supportedProps, true)) {
                continue;
            }

            if ($propValue instanceof SerializerInterface) {
                if (!$propValue->isEmpty()) {
                    return false;
                }
            } elseif (!empty($propValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (!empty($this->supportedProps)) {
            foreach ($data as $propName => $propValue) {
                if (!\in_array($propName, $this->supportedProps, true)) {
                    unset($data->$propName);
                }
            }
        }
        if (!empty($data->customer_service_instructions)) {
            $data->customer_service_instructions = [$this->getCustomerServiceInstruction()];
        }
        foreach (['return_url', 'cancel_url', 'brand_name', 'customer_service_instructions'] as $item) {
            if (empty($data->$item)) {
                unset($data->$item);
            }
        }

        return $data;
    }
}
