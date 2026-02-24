<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productdescription
 * @package Plugin\jtl_search\ExportModules
 */
class Productdescription extends Document
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
    protected $cDescription;

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->cDescription = $this->prepareString($description);

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
    public function getDescription()
    {
        return $this->cDescription;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
