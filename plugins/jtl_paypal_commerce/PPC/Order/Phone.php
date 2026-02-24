<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Phone
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Phone extends JSON
{
    /**
     * Phone constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'national_number' => '',
            'country_code'    => '49',
        ]);

        if (!empty($this->data->country_code)) {
            $this->setCountryCode($this->data->country_code);
        }
        if (!empty($this->data->national_number)) {
            $this->setNationalNumber($this->data->national_number);
        }
    }

    /**
     * @param string $phoneNumber
     * @return string
     */
    private function clearNumber(string $phoneNumber): string
    {
        return \str_replace([' ', '-', '(', ')', '#', '*'], '', $phoneNumber);
    }

    /**
     * @param string $countryAddition
     * @return string
     */
    public function getCountryCode(string $countryAddition = '+'): string
    {
        return empty($this->data->country_code) ? '' : $countryAddition . $this->data->country_code;
    }

    /**
     * @param string $countryCode
     * @return self
     * @throws InvalidPhoneException
     */
    public function setCountryCode(string $countryCode): self
    {
        $countryCode = \str_replace(' ', '', $countryCode);
        if ($countryCode === '') {
            // ToDo: set country dependend phone country code
            $countryCode = '49';
        }
        if (!\preg_match('/^(\+|0{2})?([1-9]\d*)$/', $countryCode, $hits)) {
            throw new InvalidPhoneException('phone country code is invalid', 1);
        }

        $this->data->country_code = $hits[2];

        return $this;
    }

    /**
     * @param string $trailing
     * @return string
     */
    public function getNationalNumber(string $trailing = '0'): string
    {
        return empty($this->data->national_number) ? '' : $trailing . $this->data->national_number;
    }

    /**
     * @param string $phoneNumber
     * @return self
     * @throws InvalidPhoneException
     */
    public function setNationalNumber(string $phoneNumber): self
    {
        $phoneNumber = $this->clearNumber($phoneNumber);
        if (!\preg_match('/^0?([1-9]\d*)$/', $phoneNumber, $hits)) {
            throw new InvalidPhoneException('national phone number is invalid', 2);
        }

        $this->data->national_number = $hits[1];

        return $this;
    }

    /**
     * @param string $countryAddition
     * @param string $spacer
     * @return string
     */
    public function getNumber(string $countryAddition = '+', string $spacer = ' '): string
    {
        $countryCode    = $this->getCountryCode($countryAddition);
        $nationalNumber = $this->getNationalNumber();
        if ($countryCode === '') {
            return $nationalNumber;
        }

        return $nationalNumber === '' ? '' : $countryCode . $this->getNationalNumber($spacer);
    }

    /**
     * @param string|null $phoneNumber
     * @return self
     */
    public function setNumber(?string $phoneNumber): self
    {
        $phoneNumber = $this->clearNumber($phoneNumber);
        if (empty($phoneNumber)) {
            $this->data->national_number = '';
            $this->data->country_code    = '49';

            return $this;
        }

        $countryCodes = [
            '1',
            '20|2[1-9]\d',
            '3[0-469]|3[578]\d',
            '4[013-9]|42[01]',
            '5[1-8]|5[09]\d',
            '6[7-9]\d|6[0-6]',
            '7',
            '8[1-469]|8[578]\d',
            '9[0-58]|9[679]\d',
        ];
        $numberRegEx  = '/^(\+|0{2})?(' . \implode('|', $countryCodes) . ')?(\d{1,14})$/';
        if (!\preg_match($numberRegEx, $phoneNumber, $hits)) {
            throw new InvalidPhoneException('phone number is invalid', 3);
        }

        try {
            $this->setCountryCode($hits[2]);
            $this->setNationalNumber($hits[3]);
        } catch (InvalidPhoneException $e) {
            throw new InvalidPhoneException('phone number is invalid', $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return parent::isEmpty() || empty($this->getNumber());
    }
}
