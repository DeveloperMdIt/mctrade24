<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productcategory
 * @package Plugin\jtl_search\ExportModules
 */
class Productcategory extends Document
{
    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @var int
     */
    protected $kCategory;

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
     * @return int
     */
    public function getProduct()
    {
        return $this->kProduct;
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
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
