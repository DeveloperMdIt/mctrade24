<?php

namespace Plugin\jtl_search\ExportModules;

/**
 * Class Product
 * @package Plugin\jtl_search\ExportModules
 */
class Product extends Document implements IDocument
{
    /**
     * @var int
     */
    protected $kProduct;

    /**
     * @var int
     */
    protected $kMasterId;

    /**
     * @var string
     */
    protected $cArticleNumber;

    /**
     * @var string
     */
    protected $cPictureURL;

    /**
     * @var string
     */
    protected $kManufacturer;

    /**
     * @var int
     */
    protected $nSalesRank;

    /**
     * @var int
     */
    protected $nAvailability;

    /**
     * @var string
     */
    protected $cEAN;

    /**
     * @var string
     */
    protected $cISBN;

    /**
     * @var string
     */
    protected $cMPN;

    /**
     * @var string
     */
    protected $cUPC;

    /**
     * @var array
     */
    protected $oName_arr;

    /**
     * @var array
     */
    protected $oShortDescription_arr;

    /**
     * @var array
     */
    protected $oDescription_arr;

    /**
     * @var array
     */
    protected $oPrice_arr;

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
    protected $oCategory_arr;

    /**
     * @var array
     */
    protected $oAttribute_arr;

    /**
     * @var array
     */
    protected $oVariation_arr;

    /**
     * @inheritdoc
     */
    public function setId($nId)
    {
        $this->kProduct = (int)$nId;

        return $this;
    }

    /**
     * @param int $kMasterId
     * @return $this
     */
    public function setMasterId(int $kMasterId): self
    {
        $this->kMasterId = $kMasterId;

        return $this;
    }

    /**
     * @param string $cArticleNumber
     * @return $this
     */
    public function setArticleNumber($cArticleNumber): self
    {
        $this->cArticleNumber = $cArticleNumber;

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
     * @param int $kManufacturer
     * @return $this
     */
    public function setManufacturer(int $kManufacturer): self
    {
        $this->kManufacturer = $kManufacturer;

        return $this;
    }

    /**
     * @param int $nSalesRank
     * @return $this
     */
    public function setSalesRank(int $nSalesRank): self
    {
        $this->nSalesRank = $nSalesRank;

        return $this;
    }

    /**
     * @param int $availability
     * @return $this
     */
    public function setAvailability($availability)
    {
        $this->nAvailability = (int)\ceil($availability);

        return $this;
    }

    /**
     * @param string $ean
     * @return $this
     */
    public function setEAN($ean)
    {
        $this->cEAN = $ean;

        return $this;
    }

    /**
     * @param string $isbn
     * @return $this
     */
    public function setISBN($isbn): self
    {
        $this->cISBN = $isbn;

        return $this;
    }

    /**
     * @param string $mpn
     * @return $this
     */
    public function setMPN($mpn): self
    {
        $this->cMPN = $mpn;

        return $this;
    }

    /**
     * @param string $cUPC
     * @return $this
     */
    public function setUPC($cUPC): self
    {
        $this->cUPC = $cUPC;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setName($name, $languageISO)
    {
        if (!empty($name) && !empty($languageISO)) {
            $productName = new Productname();
            $productName->setLanguageIso($languageISO)
                ->setName($name)
                ->setProduct($this->getId());

            $this->oName_arr[] = $productName;
            unset($productName);
        }

        return $this;
    }

    /**
     * @param string $languageISO
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription, $languageISO): self
    {
        if (isset($shortDescription, $languageISO) && !empty($shortDescription) && !empty($languageISO)) {
            $productShortDescription = new Productshortdescription();
            $productShortDescription->setLanguageIso($languageISO)
                ->setShortDescription($shortDescription)
                ->setProduct($this->getId());

            $this->oShortDescription_arr[] = $productShortDescription;
            unset($productShortDescription);
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
            $productDescription = new Productdescription();
            $productDescription->setDescription($description)
                ->setLanguageIso($languageISO)
                ->setProduct($this->getId());

            $this->oDescription_arr[] = $productDescription;
            unset($productDescription);
        }

        return $this;
    }

    /**
     * @param string      $currencyIso
     * @param int         $userGroupID
     * @param float       $price
     * @param null|string $basePrice
     * @return $this
     */
    public function setPrice($currencyIso, $userGroupID, $price, $basePrice = null): self
    {
        if (!empty($currencyIso) && !empty($userGroupID)) {
            $productPrice = new Productprice();
            $productPrice->setCurrencyIso($currencyIso)
                ->setPrice($price)
                ->setUserGroup($userGroupID)
                ->setProduct($this->getId());
            if ($basePrice !== null && \strlen($basePrice) > 0) {
                $productPrice->setBasePrice($basePrice);
            }
            $this->oPrice_arr[] = $productPrice;
            unset($productPrice);
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
            $productKeyword = new Productkeyword();
            $productKeyword->setKeywords($keywords)
                ->setLanguageIso($languageISO)
                ->setProduct($this->getId());

            $this->oKeywords_arr[] = $productKeyword;
            unset($productKeyword);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setURL($url, $languageISO): self
    {
        if (!empty($url) && !empty($languageISO)) {
            $productUrl = new Producturl();
            $productUrl->setLanguageIso($languageISO)
                ->setUrl($url)
                ->setProduct($this->getId());

            $this->oURL_arr[] = $productUrl;
            unset($productUrl);
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @param string $languageISO
     * @return $this
     */
    public function setAttribute($attribute, $value, $languageISO): self
    {
        if (!empty($attribute)
            && !empty($value)
            && !empty($languageISO)
        ) {
            $productAttribute = new Productattribut();
            $productAttribute->setKey($attribute)
                ->setValue($value)
                ->setLanguageIso($languageISO)
                ->setProduct($this->getId());

            $this->oAttribute_arr[] = $productAttribute;
            unset($productAttribute);
        }

        return $this;
    }

    /**
     * @param string $cVariation
     * @param string $cValue
     * @param string $languageIso
     * @return $this
     */
    public function setVariation($cVariation, $cValue, $languageIso): self
    {
        if (!empty($cVariation)
            && !empty($cValue)
            && !empty($languageIso)
        ) {
            $productVariation = new Productvariation();
            $productVariation->setKey($cVariation)
                ->setValue($cValue)
                ->setLanguageIso($languageIso)
                ->setProduct($this->getId());

            $this->oVariation_arr[] = $productVariation;
            unset($productVariation);
        }

        return $this;
    }

    /**
     * @param int $categoryID
     * @return $this
     */
    public function setCategory(int $categoryID): self
    {
        if ($categoryID > 0) {
            $productCategory = new Productcategory();
            $productCategory->setCategory($categoryID)
                ->setProduct($this->getId());

            $this->oCategory_arr[] = $productCategory;
            unset($productCategory);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->kProduct;
    }

    /**
     * @return int
     */
    public function getMasterId()
    {
        return $this->kMasterId;
    }

    /**
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->cArticleNumber;
    }

    /**
     * @return string
     */
    public function getPictureURL()
    {
        return $this->cPictureURL;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->kManufacturer;
    }

    /**
     * @return int
     */
    public function getSalesRank()
    {
        return $this->nSalesRank;
    }

    /**
     * @return int
     */
    public function getAvailability()
    {
        return $this->nAvailability;
    }

    /**
     * @return string
     */
    public function getEAN()
    {
        return $this->cEAN;
    }

    /**
     * @return string
     */
    public function getISBN()
    {
        return $this->cISBN;
    }

    /**
     * @return string
     */
    public function getMPN()
    {
        return $this->cMPN;
    }

    /**
     * @return string
     */
    public function getUPC()
    {
        return $this->cUPC;
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
    public function getShortDescription($languageISO = null)
    {
        if ($languageISO !== null) {
            foreach ($this->oShortDescription_arr as $shortDescription) {
                if (\strtolower($shortDescription->cLanguageIso) === \strtolower($languageISO)) {
                    return $shortDescription;
                }
            }
        }

        return $this->oShortDescription_arr;
    }

    /**
     * @param int|null $customerGroupID
     * @return object|array
     */
    public function getPrice(int $customerGroupID = null)
    {
        if ($customerGroupID !== null) {
            foreach ($this->oPrice_arr as $price) {
                if ((int)$price->kUserGroup === $customerGroupID) {
                    return $price;
                }
            }
        }

        return $this->oPrice_arr;
    }

    /**
     * @param string|null $languageISO
     * @return string|array
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
     * @return array
     */
    public function getAttributes()
    {
        return $this->oAttribute_arr;
    }

    /**
     * @return array
     */
    public function getVariation()
    {
        return $this->oVariation_arr;
    }

    /**
     * @inheritdoc
     */
    public function getClassName(): string
    {
        return __CLASS__;
    }
}
