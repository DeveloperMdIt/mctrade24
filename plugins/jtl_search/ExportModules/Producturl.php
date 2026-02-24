<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Producturl
 * @package Plugin\jtl_search\ExportModules
 */
class Producturl extends Document
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
    protected $cUrl;

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->cUrl = $url;

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
