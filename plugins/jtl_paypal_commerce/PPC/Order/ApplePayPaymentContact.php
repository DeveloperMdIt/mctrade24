<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Helpers\Text;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ApplePayPaymentContact
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class ApplePayPaymentContact extends JSON
{
    /**
     * @param object                 $source
     * @param ApplePayPaymentContact $target
     * @return void
     */
    private static function copyAddress(object $source, self $target): void
    {
        if (!empty($source->cNachname)) {
            $target->setFamilyName(Text::unhtmlentities($source->cNachname));
        }
        if (!empty($source->cVorname)) {
            $target->setGivenName(Text::unhtmlentities($source->cVorname));
        }
        if (!empty($source->cPLZ)) {
            $target->setPostalCode(Text::unhtmlentities($source->cPLZ));
        }
        if (!empty($source->cOrt)) {
            $target->setLocality(Text::unhtmlentities($source->cOrt));
        }
        if (!empty($source->cStrasse)) {
            $target->setStreet(\trim(Text::unhtmlentities($source->cStrasse)
                . ' '
                . Text::unhtmlentities($source->cHausnummer ?? '')));
        }
        if (!empty($source->cLand) && !$target->isEmpty()) {
            $target->setCountryCode($source->cLand);
        }
        if (!empty($source->cAdressZusatz)) {
            $target->setAdditionalAddressLine($source->cAdressZusatz);
        }
    }

    /**
     * @param Lieferadresse $shippingAddress
     * @return self
     */
    public static function fromShippingAddress(Lieferadresse $shippingAddress): self
    {
        $instance = new self((object)[
            'addressLines' => [],
        ]);
        self::copyAddress($shippingAddress, $instance);

        return $instance;
    }

    /**
     * @param Customer $customer
     * @return self
     */
    public static function fromCustomer(Customer $customer): self
    {
        $instance = new self((object)[
            'addressLines' => [],
        ]);
        self::copyAddress($customer, $instance);

        return $instance;
    }

    /**
     * @param string $givenName
     * @return self
     */
    public function setGivenName(string $givenName): self
    {
        $this->data->givenName = $givenName;

        return $this;
    }

    /**
     * @param string $familyName
     * @return self
     */
    public function setFamilyName(string $familyName): self
    {
        $this->data->familyName = $familyName;

        return $this;
    }

    /**
     * @param string $locality
     * @return self
     */
    public function setLocality(string $locality): self
    {
        $this->data->locality = $locality;

        return $this;
    }

    /**
     * @param string $postalCode
     * @return self
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->data->postalCode = $postalCode;

        return $this;
    }

    /**
     * @param string $countryCode
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->data->countryCode = $countryCode;

        return $this;
    }

    /**
     * @param string $street
     * @return self
     */
    public function setStreet(string $street): self
    {
        if (\is_array($this->data->addressLines)) {
            $this->data->addressLines[0] = $street;
        } else {
            $this->data->addressLines = [0 => $street];
        }

        return $this;
    }

    public function setAdditionalAddressLine(string $line1): self
    {
        if (\is_array($this->data->addressLines)) {
            $this->data->addressLines[1] = $line1;
        } else {
            $this->data->addressLines = [1 => $line1];
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data->$key);
            }
        }

        return $data;
    }
}
