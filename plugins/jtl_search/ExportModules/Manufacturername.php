<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Manufacturername
 * @package Plugin\jtl_search\ExportModules
 */
class Manufacturername extends Document
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
    protected $cName;

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
    public function getManufacturer()
    {
        return $this->kManufacturer;
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
