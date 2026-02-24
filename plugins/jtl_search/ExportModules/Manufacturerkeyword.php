<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Manufacturerkeyword
 * @package Plugin\jtl_search\ExportModules
 */
class Manufacturerkeyword extends Document
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
    protected $cKeywords;

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
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords): self
    {
        $this->cKeywords = $this->prepareString($keywords);

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
    public function getKeywords()
    {
        return $this->cKeywords;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
