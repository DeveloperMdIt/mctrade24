<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Category
 * @package Plugin\jtl_search\ExportModules
 */
class Category extends Document implements IDocument
{
    /**
     * @var int
     */
    protected $kCategory;

    /**
     * @var int
     */
    protected $kMasterCategory;

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
     * @var array
     */
    protected $oVisibility_arr;

    /**
     * @inheritdoc
     */
    public function setId($nId)
    {
        $this->kCategory = (int)$nId;

        return $this;
    }

    /**
     * @param int $kMasterCategory
     * @return $this
     */
    public function setMasterCategory(int $kMasterCategory): self
    {
        $this->kMasterCategory = $kMasterCategory;

        return $this;
    }

    /**
     * @param string $imageURL
     * @return $this
     */
    public function setPictureURL($imageURL): self
    {
        $this->cPictureURL = $imageURL;

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
    public function setName($name, $languageISO)
    {
        if (!empty($name) && !empty($languageISO)) {
            $categoryName = new Categoryname();
            $categoryName->setLanguageIso($languageISO)
                ->setName($name)
                ->setCategory($this->getId());

            $this->oName_arr[] = $categoryName;
            unset($categoryName);
        }

        return $this;
    }

    /**
     * @param string $languageISO
     * @param string $description
     * @return $this
     */
    public function setDescription($description, $languageISO): self
    {
        if (!empty($description) && !empty($languageISO)) {
            $categoryDescription = new Categorydescription();
            $categoryDescription->setLanguageIso($languageISO)
                ->setDescription($description)
                ->setCategory($this->getId());

            $this->oDescription_arr[] = $categoryDescription;
            unset($categoryDescription);
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
            $categoryKeyword = new Categorykeyword();
            $categoryKeyword->setLanguageIso($languageISO)
                ->setKeywords($keywords)
                ->setCategory($this->getId());

            $this->oKeywords_arr[] = $categoryKeyword;
            unset($categoryKeyword);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setURL($url, $languageISO)
    {
        if (!empty($url) && !empty($languageISO)) {
            $categoryURL = new Categoryurl();
            $categoryURL->setLanguageIso($languageISO)
                ->setUrl($url)
                ->setCategory($this->getId());

            $this->oURL_arr[] = $categoryURL;
            unset($categoryURL);
        }

        return $this;
    }

    /**
     * @param bool $visible
     * @param int  $customerGroupID
     * @return $this
     */
    public function setVisibility($visible, $customerGroupID): self
    {
        if ($visible) {
            $categoryVisibility = new Categoryvisibility();
            $categoryVisibility->setUserGroup($customerGroupID)
                ->setCategory($this->getId());

            $this->oVisibility_arr[] = $categoryVisibility;
            unset($categoryVisibility);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->kCategory;
    }

    /**
     * @return int
     */
    public function getMasterCategory()
    {
        return $this->kMasterCategory;
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
    public function getKeywords(?string $languageISO = null)
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
     * @return array
     */
    public function getVisibility()
    {
        return $this->oVisibility_arr;
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
