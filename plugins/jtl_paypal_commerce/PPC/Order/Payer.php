<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use DateTime;
use Exception;
use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Payer
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Payer extends JSON
{
    public const PAYER_NAME     = 0x01;
    public const PAYER_LOCATION = 0x02;
    public const PAYER_EMAIL    = 0x04;
    public const PAYER_PHONE    = 0x08;
    public const PAYER_BIRTH    = 0x10;
    public const PAYER_ADRESS   = self::PAYER_NAME | self::PAYER_LOCATION;
    public const PAYER_DEFAULT  = self::PAYER_ADRESS | self::PAYER_EMAIL;
    public const PAYER_CONTACT  = self::PAYER_EMAIL | self::PAYER_PHONE;
    public const PAYER_ALL      = self::PAYER_ADRESS | self::PAYER_CONTACT | self::PAYER_BIRTH;

    /**
     * Payer constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $adressData = $this->getData()->address ?? null;
        $phoneData  = $this->getData()->phone ?? null;
        if ($adressData !== null && !($adressData instanceof Address)) {
            $this->setAddress(new Address($adressData));
        }
        if ($phoneData !== null && !($phoneData instanceof Phone)) {
            $this->setPhone(new Phone($phoneData));
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPayerid(): ?string
    {
        return $this->data->payer_id ?? null;
    }

    /**
     * @param string|null $payerId
     * @return Payer
     */
    public function setPayerId(?string $payerId): self
    {
        static $pattern = '/^[2-9A-HJ-NP-Z]{13}$/';

        if ($payerId === null) {
            unset($this->data->payer_id);
        } else {
            if (!\preg_match($pattern, $payerId)) {
                throw new InvalidArgumentException(\sprintf('%s is not a valid payer id.', $payerId));
            }

            $this->data->payer_id = $payerId;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->data->email_address ?? ($this->data->email ?? null);
    }

    /**
     * @param string|null $email
     * @return Payer
     */
    public function setEmail(?string $email): self
    {
        if ($email === null) {
            unset($this->data->email_address);
        } else {
            $this->data->email_address = \mb_substr($email, 0, 254);
        }

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        return $this->data->address ?? null;
    }

    /**
     * @param Address|null $address
     * @return Payer
     */
    public function setAddress(?Address $address): self
    {
        if ($address === null) {
            unset($this->data->address);
        } else {
            $this->data->address = $address;
        }

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthDate(): ?DateTime
    {
        try {
            return isset($this->data->birth_date) ? new DateTime($this->data->birth_date) : null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param DateTime|null $birthDate
     * @return Payer
     */
    public function setBirthDate(?DateTime $birthDate): self
    {
        if ($birthDate === null) {
            unset($this->data->birth_date);
        } else {
            $this->data->birth_date = $birthDate->format('Y-m-d');
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return isset($this->data->name) ? $this->data->name->given_name ?? null : null;
    }

    /**
     * @param string|null $givenName
     * @return Payer
     */
    public function setGivenName(?string $givenName): self
    {
        if ($givenName === null) {
            unset($this->data->name->given_name);
        } elseif (isset($this->data->name)) {
            $this->data->name->given_name = \mb_substr($givenName, 0, 140);
        } else {
            $this->data->name = (object)[
                'given_name' => \mb_substr($givenName, 0, 140),
            ];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return isset($this->data->name) ? $this->data->name->surname ?? '' : '';
    }

    /**
     * @param string $surname
     * @return Payer
     */
    public function setSurname(string $surname): self
    {
        if (isset($this->data->name)) {
            $this->data->name->surname = \mb_substr($surname, 0, 140);
        } else {
            $this->data->name = (object)[
                'surname' => \mb_substr($surname, 0, 140),
            ];
        }

        return $this;
    }

    /**
     * @return Phone
     */
    public function getPhone(): Phone
    {
        $phone = new Phone();
        if (isset($this->data->phone->phone_number->national_number)) {
            $phone->setNumber($this->data->phone->phone_number->national_number);
        }

        return $phone;
    }

    /**
     * @param Phone|null $phone
     * @return self
     */
    public function setPhone(?Phone $phone): self
    {
        if ($phone === null) {
            unset($this->data->phone);
        } else {
            $this->data->phone = (object)[
                'phone_number' => (object)['national_number' => $phone->getNumber('00', '')]
            ];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->phone->phone_number->national_number)) {
            unset($data->phone);
        }

        return $data;
    }
}
