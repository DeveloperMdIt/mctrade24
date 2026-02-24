<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Manufacturerurl
 * @package Plugin\jtl_search\ExportModules
 */
class Manufacturerurl extends Document
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
    protected $cUrl;

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
     * @param string $url
     * @return $this
     */
    public function setUrl($url): self
    {
        $this->cUrl = $url;

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
    public function getUrl()
    {
        return $this->cUrl;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
