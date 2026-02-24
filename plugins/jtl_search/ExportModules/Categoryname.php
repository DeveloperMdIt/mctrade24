<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Categoryname
 * @package Plugin\jtl_search\ExportModules
 */
class Categoryname extends Document
{
    /**
     * @var int
     */
    protected $kCategory;

    /**
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @param int $kCategory
     * @return $this
     */
    public function setCategory(int $kCategory): self
    {
        $this->kCategory = $kCategory;

        return $this;
    }

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
    public function getCategory()
    {
        return $this->kCategory;
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
