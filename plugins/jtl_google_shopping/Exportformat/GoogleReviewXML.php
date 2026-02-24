<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Exportformat;

use DateTime;
use Exception;
use stdClass;

/**
 * Class GoogleReviewXML
 * @package Plugin\jtl_google_shopping\Exportformat
 */
class GoogleReviewXML extends GoogleShoppingXML
{
    protected string $header = '<?xml version="1.0" encoding="UTF-8"?>' . "\r"
    . '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" '
    . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
    . 'xsi:noNamespaceSchemaLocation='
    . '"http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">' . "\r"
    . "\t" . '<version>2.3</version>' . "\r"
    . "\t" . '<aggregator>' . "\r"
    . "\t\t" . '<name>JTL-Shop</name>' . "\r"
    . "\t" . '</aggregator>' . "\r"
    . "\t" . '<publisher>' . "\r"
    . "\t\t" . '<name><![CDATA[###cShop###]]></name>' . "\r"
    . "\t" . '</publisher>' . "\r"
    . "\t" . '<reviews>' . "\r";

    protected string $footer = "\t</reviews>\r</feed>";

    /**
     * @inheritdoc
     */
    public function loadAttr(): self
    {
        return $this;
    }

    public function writeFooter(): self
    {
        \fwrite($this->tmpFile, $this->footer);
        \fclose($this->tmpFile);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function writeContent(): self
    {
        if (!\is_array($this->exportProducts) || !\is_array($this->attributes)) {
            return $this;
        }
        foreach ($this->exportProductIDs as $productID) {
            $this->loadProduct($productID);
            $this->writeReview($this->exportProducts[$productID] ?? new Product());
            unset($this->exportProducts[$productID]);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    private function loadProduct(int $productID): self
    {
        if ($productID <= 0) {
            return $this;
        }
        $opt                              = Product::getExportOptions();
        $opt->nRatings                    = 1;
        $product                          = new Product();
        $this->exportProducts[$productID] = $product;
        try {
            $product->fuelleArtikel(
                $productID,
                $opt,
                $this->exportformat->kKundengruppe,
                $this->exportformat->kSprache,
                $this->exportformat->nUseCache !== 1
            );
        } catch (Exception) {
            unset($product);

            return $this;
        }
        if ($product->kArtikel === null) {
            unset($this->exportProducts[$productID]);
            $this->logger->notice(
                \sprintf(
                    \__('Product could not be exported because no product exists for current settings'),
                    $productID
                )
            );

            return $this;
        }

        if ((int)$product->nIstVater === 0 && $product->kVaterArtikel > 0) {
            $this->loadProduct($product->kVaterArtikel);
            if (isset($this->exportProducts[$product->kVaterArtikel]->kArtikel)) {
                if ((int)$this->settings->get('ext_artnr_child') === 1) {
                    $product->cArtNr .= '_' . $product->kArtikel;
                }
                $product->cVaterArtNr = $this->exportProducts[$product->kVaterArtikel]->cArtNr;
                unset($this->exportProducts[$product->kVaterArtikel]);
            } else {
                unset(
                    $this->exportProducts[$productID],
                    $this->exportProducts[$product->kVaterArtikel]
                );
                $this->logger->notice(
                    \sprintf(\__('Product could not be exported because no parent product exists'), $productID)
                );

                return $this;
            }
        }
        if ($this->settings['strip_tags'] ?? 'N' === 'Y') {
            $product->cName = \strip_tags($product->cName);
        }
        $product->cName = \mb_substr($product->cName, 0, self::MAX_PRODUCT_NAME_LENGTH);

        return $this;
    }

    private function getGtin(Product $product): string
    {
        $product->cGtin = '';
        if (!empty($product->cBarcode)) {
            $product->cGtin = $product->cBarcode;
        } elseif (!empty($product->cISBN)) {
            $product->cGtin = $product->cISBN;
        }
        if (empty($product->cGtin)) {
            return '';
        }
        $prefix = "\t\t\t\t\t\t";

        return $prefix . "<gtins>\r"
            . $prefix . "\t<gtin>" . $product->cGtin . "</gtin>\r"
            . $prefix . "</gtins>\r";
    }

    private function getBrand(Product $product): string
    {
        if (empty($product->cHersteller)) {
            return '';
        }
        $prefix = "\t\t\t\t\t\t";

        return $prefix . "<brands>\r"
            . $prefix . "\t<brand><![CDATA[" . $product->cHersteller . "]]></brand>\r"
            . $prefix . "</brands>\r";
    }

    private function getAsin(Product $product): string
    {
        if (empty($product->cASIN)) {
            return '';
        }
        $prefix = "\t\t\t\t\t\t";

        return $prefix . "<asins>\r"
            . $prefix . "\t<asin>" . $product->cASIN . "</asin>\r"
            . $prefix . "</asins>\r";
    }

    private function getMpn(Product $product): string
    {
        if (empty($product->cHAN)) {
            return '';
        }
        $prefix = "\t\t\t\t\t\t";

        return $prefix . "<mpns>\r"
            . $prefix . "\t<mpn>" . $product->cHAN . "</mpn>\r"
            . $prefix . "</mpns>\r";
    }

    private function getSku(Product $product): string
    {
        if (empty($product->cArtNr)) {
            return '';
        }
        $prefix = "\t\t\t\t\t\t";

        return $prefix . "<skus>\r"
            . $prefix . "\t<sku>" . $product->cArtNr . "</sku>\r"
            . $prefix . "</skus>\r";
    }

    private function sanitize(string $content): string
    {
        return \html_entity_decode($content);
    }

    private function getReviewXML(stdClass $rating): string
    {
        $prefix  = "\t\t\t";
        $prefix2 = "\t\t\t\t";

        if ($rating->kKunde > 0 && $rating->cName !== 'Anonym') {
            $name = '<name><![CDATA[' . $this->sanitize($rating->cName) . ']]></name>';
        } else {
            $name = '<name is_anonymous="true"><![CDATA[' . $this->sanitize($rating->cName) . ']]></name>';
        }

        return $prefix . '<review_id>' . $rating->kBewertung . '</review_id>' . "\r"
            . $prefix . '<reviewer>' . "\r"
            . $prefix2 . $name . "\r"
            . $prefix . '</reviewer>' . "\r"
            . $prefix . '<review_timestamp>'
            . (new DateTime($rating->dDatum))->format('Y-m-d\TH:i:sP')
            . '</review_timestamp>' . "\r"
            . $prefix . '<title><![CDATA[' . $this->sanitize($rating->cTitel) . ']]></title>' . "\r"
            . $prefix . '<content><![CDATA['
            . $this->sanitize(\strip_tags(\rtrim($rating->cText))) . ']]></content>' . "\r"
            . $prefix . '<review_url type="group">' . $rating->url . '</review_url>' . "\r"
            . $prefix . '<ratings>' . "\r"
            . $prefix2 . '<overall min="1" max="5">' . $rating->nSterne . '</overall>' . "\r"
            . $prefix . '</ratings>' . "\r";
    }

    private function isValidReview(stdClass $review): bool
    {
        return !empty($review->cName)
            && !empty($review->cText)
            && !empty($review->cTitel)
            && $review->nSterne > -1
            && $review->nSterne < 6
            && $review->kSprache === $this->exportformat->kSprache;
    }

    public function writeReview(Product $product): self
    {
        if (
            $product->kArtikel < 1
            || $product->Bewertungen === null
            || \count($product->Bewertungen->oBewertung_arr) === 0
        ) {
            return $this;
        }
        $prefix     = "\t\t\t";
        $prefix2    = "\t\t\t\t";
        $productXML = $prefix . "<products>\r"
            . $prefix2 . "<product>\r"
            . $prefix2 . "\t<product_ids>\r"
            . $this->getGtin($product)
            . $this->getMpn($product)
            . $this->getSku($product)
            . $this->getBrand($product)
            . $this->getAsin($product)
            . $prefix2 . "\t</product_ids>\r"
            . $prefix2 . "\t<product_name><![CDATA[" . $this->sanitize($product->cName) . "]]></product_name>\r"
            . $prefix2 . "\t<product_url>" . $product->cURLFull . "</product_url>\r"
            . $prefix2 . "</product>\r"
            . $prefix . "</products>\r";
        $xml        = '';
        $url        = $product->cURLFull . '#tab-votes';
        foreach ($product->Bewertungen->oBewertung_arr as $rating) {
            if (!$this->isValidReview($rating)) {
                continue;
            }
            $rating->url = $url;

            $xml .= "\t\t<review>\r";
            $xml .= $this->getReviewXML($rating);
            $xml .= $productXML;
            $xml .= "\t\t</review>\r";
        }
        \fwrite($this->tmpFile, $xml);

        return $this;
    }
}
