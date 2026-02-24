<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Productkeyword
 * @package Plugin\jtl_search\ExportModules
 */
class Productkeyword extends Document
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
    protected $cKeywords;

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
    public function getProduct()
    {
        return $this->kProduct;
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
