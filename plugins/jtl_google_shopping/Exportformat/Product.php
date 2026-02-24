<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Exportformat;

use JTL\Export\Product as ExportProduct;

use function Functional\select;

/**
 * Class Product
 * @package Plugin\jtl_google_shopping\Exportformat
 */
class Product extends ExportProduct
{
    /**
     * @return array
     * @note this has to exist even if it should be idential to parent's - otherwise there would be errors like:
     * "returned as member variable from __sleep() but does not exist"
     */
    public function __sleep()
    {
        return select(\array_keys(\get_object_vars($this)), static function ($e) {
            return $e !== 'conf' && $e !== 'db';
        });
    }

    /**
     * @var string
     */
    public $cVaterArtNr;

    /**
     * @var string
     */
    public $cGroesse;

    /**
     * @var string
     */
    public $cFarbe;

    /**
     * @var string
     */
    public $cGeschlecht;

    /**
     * @var string
     */
    public $cAltersgruppe;

    /**
     * @var string
     */
    public $cMuster;

    /**
     * @var string
     */
    public $cMaterial;

    /**
     * @var string
     */
    public $cVerfuegbarkeit;
    /**
     * @var array
     */
    public $cArtikelbild_arr = [];

    /**
     * @var string
     */
    public $cGtin;

    /**
     * @var string
     */
    public $salePriceEffectiveDate;

    /**
     * @var string
     */
    public $unitPricingMeasure;

    /**
     * @var string
     */
    public $unitPricingBaseMeasure;

    /**
     * @var string
     */
    public $cLieferland;

    /**
     * @var array
     */
    public $cCategorie_arr = [];

    /**
     * @var array
     */
    public $cGoogleCategorie = [];

    /**
     * @var string
     */
    public $cZustand;

    /**
     * @var string
     */
    public $bIsBundle;

    /**
     * @var float
     */
    public $salePrice;

    /**
     * @var float
     */
    public $fVKBrutto;

    /**
     * @var string|null
     */
    public $release;
}
