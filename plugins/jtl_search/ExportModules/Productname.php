<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productname
 * @package Plugin\jtl_search\ExportModules
 */
class Productname extends Document
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
    protected $cName;

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->cName = $this->prepareString($name);

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
    public function getName()
    {
        return $this->cName;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
