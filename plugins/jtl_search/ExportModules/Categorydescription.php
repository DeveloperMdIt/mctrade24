<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Categorydescription
 * @package Plugin\jtl_search\ExportModules
 */
class Categorydescription extends Document
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
    protected $cDescription;

    /**
     * @param int $categoryID
     * @return $this
     */
    public function setCategory(int $categoryID): self
    {
        $this->kCategory = $categoryID;

        return $this;
    }

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
    public function getCategory()
    {
        return $this->kCategory;
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
