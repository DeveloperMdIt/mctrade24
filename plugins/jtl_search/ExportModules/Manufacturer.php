<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Manufacturer
 * @package Plugin\jtl_search\ExportModules
 */
class Manufacturer extends Document implements IDocument
{
    /**
     * @var int
     */
    protected $kManufacturer;

    /**
     * @var string
     */
    protected $cPictureURL;

    /**
     * @var int
     */
    protected $nPriority;

    /**
     * @var array
     */
    protected $oName_arr;

    /**
     * @var array
     */
    protected $oDescription_arr;

    /**
     * @var array
     */
    protected $oKeywords_arr;

    /**
     * @var array
     */
    protected $oURL_arr;

    /**
     * @inheritdoc
     */
    public function setId($nId)
    {
        $this->kManufacturer = (int)$nId;

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setPictureURL($url): self
    {
        $this->cPictureURL = $url;

        return $this;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority): self
    {
        $this->nPriority = $priority;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setName($name, $languageISO): self
    {
        if (!empty($name) && !empty($languageISO)) {
            $manufacturerName = new Manufacturername();
            $manufacturerName->setLanguageIso($languageISO)
                ->setName($name)
                ->setManufacturer($this->getId());

            $this->oName_arr[] = $manufacturerName;
            unset($manufacturerName);
        }

        return $this;
    }

    /**
     * @param string|null $languageISO
     * @param string      $description
     * @return $this
     */
    public function setDescription($description, $languageISO): self
    {
        if (!empty($description) && !empty($languageISO)) {
            $manufacturerDescription = new Manufacturerdescription();
            $manufacturerDescription->setLanguageIso($languageISO)
                ->setDescription($description)
                ->setManufacturer($this->getId());

            $this->oDescription_arr[] = $manufacturerDescription;
            unset($manufacturerDescription);
        }

        return $this;
    }

    /**
     * @param string $languageISO
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords, $languageISO): self
    {
        if (!empty($keywords) && !empty($languageISO)) {
            $manufacturerKeyword = new Manufacturerkeyword();
            $manufacturerKeyword->setLanguageIso($languageISO)
                ->setKeywords($keywords)
                ->setManufacturer($this->getId());

            $this->oKeywords_arr[] = $manufacturerKeyword;
            unset($manufacturerKeyword);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setURL($url, $languageISO)
    {
        if (!empty($url) && !empty($languageISO)) {
            $manufacturerURL = new Manufacturerurl();
            $manufacturerURL->setLanguageIso($languageISO)
                ->setUrl($url)
                ->setManufacturer($this->getId());

            $this->oURL_arr[] = $manufacturerURL;
            unset($manufacturerURL);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->kManufacturer;
    }

    /**
     * @return string
     */
    public function getPictureURL()
    {
        return $this->cPictureURL;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->nPriority;
    }

    /**
     * @inheritdoc
     */
    public function getName($languageISO = null)
    {
        if ($languageISO !== null) {
            foreach ($this->oName_arr as $name) {
                if (\strtolower($name->cLanguageIso) === \strtolower($languageISO)) {
                    return $name;
                }
            }
        }

        return $this->oName_arr;
    }

    /**
     * @param string|null $languageISO
     * @return object|array
     */
    public function getDescription($languageISO = null)
    {
        if ($languageISO !== null) {
            foreach ($this->oDescription_arr as $description) {
                if (\strtolower($description->cLanguageIso) === \strtolower($languageISO)) {
                    return $description;
                }
            }
        }

        return $this->oDescription_arr;
    }

    /**
     * @param string|null $languageISO
     * @return object|array
     */
    public function getKeywords($languageISO = null)
    {
        if ($languageISO !== null) {
            foreach ($this->oKeywords_arr as $keyword) {
                if (\strtolower($keyword->cLanguageIso) === \strtolower($languageISO)) {
                    return $keyword;
                }
            }
        }

        return $this->oKeywords_arr;
    }

    /**
     * @inheritdoc
     */
    public function getURL(?string $languageISO = null)
    {
        if ($languageISO !== null) {
            foreach ($this->oURL_arr as $url) {
                if (\strtolower($url->cLanguageIso) === \strtolower($languageISO)) {
                    return $url;
                }
            }
        }

        return $this->oURL_arr;
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
