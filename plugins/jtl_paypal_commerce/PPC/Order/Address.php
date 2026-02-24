<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use JTL\Checkout\Adresse;
use JTL\Customer\Customer;
use JTL\Helpers\Text;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Address
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Address extends JSON
{
    /**
     * Address constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @param object  $source
     * @param Address $target
     */
    private static function copyAddress(object $source, self $target): void
    {
        if (!empty($source->cPLZ)) {
            $target->setPostalCode(Text::unhtmlentities($source->cPLZ));
        }
        if (!empty($source->cOrt)) {
            $target->setCity(Text::unhtmlentities($source->cOrt));
        }
        if (!empty($source->cStrasse)) {
            $target->setAddress([\trim(Text::unhtmlentities($source->cStrasse)
                . ' '
                . Text::unhtmlentities($source->cHausnummer ?? ''))]);
        }
        if (!empty($source->cLand) && !$target->isEmpty()) {
            $target->setCountryCode($source->cLand);
        }
    }

    /**
     * @param Adresse $address
     * @return Address
     */
    public static function createFromOrderAddress(Adresse $address): self
    {
        $result = new static();
        self::copyAddress($address, $result);

        return $result;
    }

    /**
     * @param Customer $customer
     * @return static
     */
    public static function createFromCustomer(Customer $customer): self
    {
        $result = new static();
        self::copyAddress($customer, $result);

        return $result;
    }

    /**
     * @return string[]
     */
    public function getAddress(): array
    {
        $result = [$this->data->address_line_1 ?? ''];

        if (!empty($this->data->address_line_2)) {
            $result[] = $this->data->address_line_2;
        }

        return $result;
    }

    /**
     * @param string[] $address
     * @return Address
     */
    public function setAddress(array $address): self
    {
        $street = \array_shift($address);
        $aptmt  = \array_shift($address);

        if ($street !== null) {
            $this->data->address_line_1 = \mb_substr($street, 0, 300);
        } else {
            unset($this->data->address_line_1);
        }
        if ($aptmt !== null) {
            $this->data->address_line_2 = \mb_substr($aptmt, 0, 300);
        } else {
            unset($this->data->address_line_2);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->data->postal_code ?? '';
    }

    /**
     * @param string $postalCode
     * @return Address
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->data->postal_code = \mb_substr($postalCode, 0, 60);

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->data->admin_area_2 ?? '';
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity(string $city): self
    {
        $this->data->admin_area_2 = \mb_substr($city, 0, 120);

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->data->country_code ?? '';
    }

    /**
     * @param string $countryCode
     * @return Address
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->data->country_code = \mb_substr($countryCode, 0, 2);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->data->admin_area_1 ?? null;
    }

    /**
     * @param string|null $state
     * @return Address
     */
    public function setState(?string $state): self
    {
        if ($state === null) {
            unset($this->data->admin_area_1);
        } else {
            $this->data->admin_area_1 = \mb_substr($state, 0, 300);
        }

        return $this;
    }
}
