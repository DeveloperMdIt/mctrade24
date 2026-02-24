<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Categoryvisibility
 * @package Plugin\jtl_search\ExportModules
 */
class Categoryvisibility extends Document
{
    /**
     * @var int
     */
    protected $kCategory;

    /**
     * @var int
     */
    protected $kUserGroup;

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
     * @param int $kUserGroup
     * @return $this
     */
    public function setUserGroup(int $kUserGroup): self
    {
        $this->kUserGroup = $kUserGroup;

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
     * @return int
     */
    public function getUserGroup()
    {
        return $this->kUserGroup;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
