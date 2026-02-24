<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use DateTime;
use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Phone;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSONData;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;
use stdClass;

/**
 * Class AbstractPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class AbstractPaymentSource extends JSON implements PaymentSourceInterface
{
    private array $supportedProps;

    /**
     * @inheritDoc
     */
    public function __construct(?object $data = null, array $supportedProps = [])
    {
        parent::__construct($data ?? (object)[]);

        $this->supportedProps = $supportedProps;
    }

    protected function serializeProperty(string $propName, mixed $data): void
    {
        $methodName = 'serialize' . \str_replace('_', '', \ucwords($propName, '_'));
        if (\method_exists($this, $methodName)) {
            $this->$methodName($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function setData(object|array|string $data): static
    {
        parent::setData($data);

        foreach ($data as $propName => $propValue) {
            if ($propValue !== null) {
                $methodName = 'initdata' . \str_replace('_', '', \ucwords($propName, '_'));
                if (\method_exists($this, $methodName)) {
                    $this->$methodName($propValue);
                }
            }
        }

        return $this;
    }

    protected function mapEntitie(string $name): string
    {
        return $name;
    }

    protected function getMappedValue(string $name): mixed
    {
        $valueName = $this->mapEntitie($name);

        return $this->getData()->$valueName ?? null;
    }

    protected function setMappedValue(string $name, mixed $value): void
    {
        $name = $this->mapEntitie($name);
        $data = $this->getData();
        if ($data === null) {
            $this->setData($data = new stdClass());
        }

        if ($value === null) {
            unset($data->$name);
        } else {
            $data->$name = $value;
        }
    }

    protected function createExperienceContext(?object $data = null): ?ExperienceContext
    {
        return $data === null ? null : $this->buildExperienceContext($data);
    }

    protected function createVaultRequest(): void
    {
        $this->invalidateProperty('attribute_vault');
    }

    protected function removeVaultRequest(): void
    {
        $this->invalidateProperty('attribute_vault');
    }

    public function isPropertyActive(string $property): bool
    {
        return empty($this->supportedProps)
            || \in_array($property, $this->supportedProps, true)
            || \in_array($this->mapEntitie($property), $this->supportedProps, true);
    }

    public function invalidateProperty(string $property): void
    {
        if (!$this->isPropertyActive($property)) {
            return;
        }

        throw new InvalidArgumentException('Property "' . $property . '" is not supported.');
    }

    public function setId(string $id): static
    {
        $this->invalidateProperty('id');

        return $this;
    }

    public function getId(): string
    {
        $this->invalidateProperty('id');

        return '';
    }

    public function setType(string $type): static
    {
        $this->invalidateProperty('type');

        return $this;
    }

    public function getType(): string
    {
        $this->invalidateProperty('type');

        return '';
    }

    public function setName(string $name): static
    {
        $this->invalidateProperty('name');

        return $this;
    }

    public function getName(): string
    {
        $this->invalidateProperty('name');

        return '';
    }

    public function setEmail(string $email): static
    {
        $this->invalidateProperty('email_address');

        return $this;
    }

    public function getEmail(): string
    {
        $this->invalidateProperty('email_address');

        return '';
    }

    public function setBirthDate(?DateTime $birthDate = null): static
    {
        $this->invalidateProperty('birth_date');

        return $this;
    }

    public function getBirthDate(): ?DateTime
    {
        $this->invalidateProperty('birth_date');

        return null;
    }

    public function setPhoneNumber(?Phone $phoneNumber = null): static
    {
        $this->invalidateProperty('phone_number');

        return $this;
    }

    public function getPhoneNumber(): ?Phone
    {
        $this->invalidateProperty('phone_number');

        return null;
    }

    public function setNumber(string $number): static
    {
        $this->invalidateProperty('number');

        return $this;
    }

    public function getNumber(): string
    {
        $this->invalidateProperty('number');

        return '';
    }

    public function setSecurityCode(string $securityCode): static
    {
        $this->invalidateProperty('security_code');

        return $this;
    }

    public function getSecurityCode(): string
    {
        $this->invalidateProperty('security_code');

        return '';
    }

    public function setExpiry(string $expiry): static
    {
        $this->invalidateProperty('expiry');

        return $this;
    }

    public function getExpiry(): string
    {
        $this->invalidateProperty('expiry');

        return '';
    }

    public function setCard(?CardDetails $cardDetails = null): static
    {
        $this->invalidateProperty('card');

        return $this;
    }

    public function getCard(): ?CardDetails
    {
        $this->invalidateProperty('card');

        return null;
    }

    public function setStoredCredential(?StoredCredential $storedCredential = null): static
    {
        $this->invalidateProperty('stored_credential');

        return $this;
    }

    public function getStoredCredential(): ?StoredCredential
    {
        $this->invalidateProperty('stored_credential');

        return null;
    }

    public function setBIC(string $bic): static
    {
        $this->invalidateProperty('bic');

        return $this;
    }

    public function getBIC(): string
    {
        $this->invalidateProperty('bic');

        return '';
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        throw new InvalidArgumentException('ExperienceContext is not supported.');
    }

    public function setExperienceContext(?ExperienceContext $experienceContext = null): static
    {
        $this->invalidateProperty('experience_context');

        return $this;
    }

    public function getExperienceContext(): ?ExperienceContext
    {
        $this->invalidateProperty('experience_context');

        return null;
    }

    public function setBillingAgreementId(string $billingAgreementId): static
    {
        $this->invalidateProperty('billing_agreement_id');

        return $this;
    }

    public function getBillingAgreementId(): string
    {
        $this->invalidateProperty('billing_agreement_id');

        return '';
    }

    public function setBillingAddress(?Address $address = null): static
    {
        $this->invalidateProperty('billing_address');

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        $this->invalidateProperty('billing_address');

        return null;
    }

    public function setVaultId(string $vaultId): static
    {
        $this->invalidateProperty('vault_id');

        return $this;
    }

    public function getVaultId(): string
    {
        $this->invalidateProperty('vault_id');

        return '';
    }

    public function applyPayer(Payer $payer): static
    {
        if ($this->isPropertyActive('name')) {
            $this->setName(\trim($payer->getGivenName() . ' ' . $payer->getSurname()));
        }
        if ($this->isPropertyActive('email_address')) {
            $this->setEmail($payer->getEmail() ?? '');
        }
        if ($this->isPropertyActive('birth_date')) {
            $this->setBirthDate($payer->getBirthDate());
        }
        if ($this->isPropertyActive('phone_number')) {
            $this->setPhoneNumber($payer->getPhone());
        }
        if ($this->isPropertyActive('billing_address')) {
            $this->setBillingAddress($payer->getAddress());
        }

        return $this;
    }

    public function fetchPayer(): Payer
    {
        $payer = new Payer();
        if (($this->getData()->account_id ?? null) !== null) {
            $payer->setPayerId($this->getData()->account_id);
        }
        if ($this->isPropertyActive('name')) {
            $nameParts = explode(' ', $this->getName(), 2);
            $payer->setGivenName($nameParts[0] ?? '');
            $payer->setSurname($nameParts[1] ?? '');
        }
        if ($this->isPropertyActive('email_address')) {
            $payer->setEmail($this->getEmail());
        }
        if ($this->isPropertyActive('birth_date')) {
            $payer->setBirthDate($this->getBirthDate());
        }
        if ($this->isPropertyActive('phone_number')) {
            $payer->setPhone($this->getPhoneNumber());
        }
        if ($this->isPropertyActive('billing_address')) {
            $payer->setAddress($this->getBillingAddress());
        }

        return $payer;
    }

    public function setCountryCode(string $cc): static
    {
        $this->invalidateProperty('country_code');

        return $this;
    }

    public function getCountryCode(): string
    {
        $this->invalidateProperty('country_code');

        return '';
    }

    public function getPaymentReference(): string
    {
        $this->invalidateProperty('payment_reference');

        return '';
    }

    public function getDepositBankDetails(): ?BankDetails
    {
        $this->invalidateProperty('deposit_bank_details');

        return null;
    }

    public function withVaultRequest(bool $with = true): static
    {
        if ($with) {
            $this->createVaultRequest();
        } else {
            $this->removeVaultRequest();
        }

        return $this;
    }

    public function setAttributes(?JSON $attributes = null): static
    {
        $this->invalidateProperty('attributes');

        return $this;
    }

    public function addAttribute(string $name, ?JSON $attribute = null): static
    {
        $this->invalidateProperty('attributes');

        return $this;
    }

    public function getAttributes(): ?JSON
    {
        $this->invalidateProperty('attributes');

        return null;
    }

    public function getAttribute(string $name): ?JSONData
    {
        $this->invalidateProperty('attributes');

        return null;
    }

    public function getProperty(array|string $property): object|string|null
    {
        $data = $this->getData();
        if ($data === null || ($data->$property ?? null) === null) {
            return null;
        }

        return $data->$property;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data       = clone parent::jsonSerialize();
        $serialized = [];

        foreach ($data as $propName => $propValue) {
            $propName = $this->mapEntitie($propName);
            if (
                empty($propValue)
                || !$this->isPropertyActive($propName)
                || ($propValue instanceof SerializerInterface && $propValue->isEmpty())
            ) {
                unset($data->$propName);
            } else {
                $this->serializeProperty($propName, $data);
                $serialized[] = $propName;
            }
        }
        foreach (\array_diff($this->supportedProps, $serialized) as $propName) {
            $this->serializeProperty($propName, $data);
        }

        return $data;
    }
}
