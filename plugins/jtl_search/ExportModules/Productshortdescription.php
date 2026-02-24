<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productshortdescription
 * @package Plugin\jtl_search\ExportModules
 */
class Productshortdescription extends Document
{
    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @var string
     */
    protected $cShortDescription;

    /**
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription): self
    {
        $this->cShortDescription = $this->prepareString($shortDescription);

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
    public function getShortDescription()
    {
        return $this->cShortDescription;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
