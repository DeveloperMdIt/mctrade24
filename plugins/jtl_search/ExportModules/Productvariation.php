<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productvariation
 * @package Plugin\jtl_search\ExportModules
 */
class Productvariation extends Document
{
    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @var string
     */
    protected $cKey;

    /**
     * @var string
     */
    protected $cValue;

    /**
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key): self
    {
        $this->cKey = $key;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value): self
    {
        $this->cValue = $this->prepareString($value);

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
     * @return string
     */
    public function getKey()
    {
        return $this->cKey;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->cValue;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
