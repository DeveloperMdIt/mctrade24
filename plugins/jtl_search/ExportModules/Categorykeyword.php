<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Categorykeyword
 * @package Plugin\jtl_search\ExportModules
 */
class Categorykeyword extends Document
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
    protected $cKeywords;

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
    public function getCategory()
    {
        return $this->kCategory;
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
