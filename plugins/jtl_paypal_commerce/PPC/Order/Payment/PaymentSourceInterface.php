<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use DateTime;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payer;
use Plugin\jtl_paypal_commerce\PPC\Order\Phone;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSONData;

/**
 * PaymentSourceInterface
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment;
 */
interface PaymentSourceInterface
{
    public function setId(string $id): static;

    public function getId(): string;

    public function setType(string $type): static;

    public function getType(): string;

    public function setName(string $name): static;

    public function getName(): string;

    public function setEmail(string $email): static;

    public function getEmail(): string;

    public function setBirthDate(?DateTime $birthDate = null): static;

    public function getBirthDate(): ?DateTime;

    public function setPhoneNumber(?Phone $phoneNumber = null): static;

    public function getPhoneNumber(): ?Phone;

    public function setNumber(string $number): static;

    public function getNumber(): string;

    public function setSecurityCode(string $securityCode): static;

    public function getSecurityCode(): string;

    public function setExpiry(string $expiry): static;

    public function getExpiry(): string;

    public function setCard(?CardDetails $cardDetails = null): static;

    public function getCard(): ?CardDetails;

    public function setStoredCredential(?StoredCredential $storedCredential = null): static;

    public function getStoredCredential(): ?StoredCredential;

    public function setBIC(string $bic): static;

    public function getBIC(): string;

    public function buildExperienceContext(?object $data = null): ExperienceContext;

    public function setExperienceContext(?ExperienceContext $experienceContext = null): static;

    public function getExperienceContext(): ?ExperienceContext;

    public function setBillingAgreementId(string $billingAgreementId): static;

    public function getBillingAgreementId(): string;

    public function setBillingAddress(?Address $address = null): static;

    public function getBillingAddress(): ?Address;

    public function setVaultId(string $vaultId): static;

    public function getVaultId(): string;

    public function applyPayer(Payer $payer): static;

    public function fetchPayer(): Payer;

    public function setCountryCode(string $cc): static;

    public function getCountryCode(): string;

    public function getPaymentReference(): string;

    public function getDepositBankDetails(): ?BankDetails;

    public function withVaultRequest(bool $with = true): static;

    public function setAttributes(?JSON $attributes = null): static;

    public function addAttribute(string $name, ?JSON $attribute = null): static;

    public function getAttributes(): ?JSON;

    public function getAttribute(string $name): ?JSONData;

    public function getProperty(string $property): object|string|null;
}
