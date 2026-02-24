<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Manufacturerdescription
 * @package Plugin\jtl_search\ExportModules
 */
class Manufacturerdescription extends Document
{
    /**
     * @var int
     */
    protected $kManufacturer;

    /**
     * @var string
     */
    protected $cLanguageIso;

    /**
     * @var string
     */
    protected $cDescription;

    /**
     * @param int $kManufacturer
     * @return $this
     */
    public function setManufacturer(int $kManufacturer): self
    {
        $this->kManufacturer = $kManufacturer;

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
    public function getManufacturer()
    {
        return $this->kManufacturer;
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
