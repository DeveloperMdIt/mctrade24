<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productprice
 * @package Plugin\jtl_search\ExportModules
 */
class Productprice extends Document
{
    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @var int
     */
    protected $kUserGroup;

    /**
     * @var string
     */
    protected $cCurrencyIso;

    /**
     * @var string
     */
    protected $cBasePrice;

    /**
     * @var float
     */
    protected $fPrice;

    /**
     * @param int $customerGroupID
     * @return $this
     */
    public function setUserGroup(int $customerGroupID): self
    {
        $this->kUserGroup = $customerGroupID;

        return $this;
    }

    /**
     * @param string $currencyISO
     * @return $this
     */
    public function setCurrencyIso($currencyISO): self
    {
        $this->cCurrencyIso = $currencyISO;

        return $this;
    }

    /**
     * @param string $cBasePrice
     * @return $this
     */
    public function setBasePrice($cBasePrice): self
    {
        $this->cBasePrice = $cBasePrice;

        return $this;
    }

    /**
     * @param float $fPrice
     * @return $this
     */
    public function setPrice($fPrice): self
    {
        $this->fPrice = (float)$fPrice;

        return $this;
    }

    /**
     * @return int
     */
    public function getProduct()
    {
        return $this->kProduct;
    }

    /**
     * @return int
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
    }

    /**
     * @return string
     */
    public function getCurrencyIso()
    {
        return $this->cCurrencyIso;
    }

    /**
     * @return string
     */
    public function getBasePrice()
    {
        return $this->cBasePrice;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->fPrice;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
