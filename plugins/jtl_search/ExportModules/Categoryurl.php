<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Categoryurl
 * @package Plugin\jtl_search\ExportModules
 */
class Categoryurl extends Document
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
    protected $cUrl;

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
    public function getCategory()
    {
        return $this->kCategory;
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
