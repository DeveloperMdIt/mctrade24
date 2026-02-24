<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\gpsr;

use Illuminate\Support\Collection;
use JTL\Catalog\Product\Artikel;
use JTL\Smarty\JTLSmarty;

/**
 * Class GPSR
 * @package Plugin\jtl_gpsr\gpsr
 */
class GPSR
{
    private array $data = [];

    private array $fields = [
        'name',
        'street',
        'housenumber',
        'postalcode',
        'city',
        'state',
        'country',
        'email',
        'homepage',
    ];

    /**
     * Handler constructor
     * @param Artikel[]|Collection $products
     */
    public function __construct($products)
    {
        $this->fillProducts($products);
    }

    /**
     * @param Artikel[]|Collection $products
     * @return void
     */
    private function fillProducts($products): void
    {
        foreach ($products as $product) {
            $this->data[$product->kArtikel] = $this->fillData($product->FunktionsAttribute);
        }
    }

    /**
     * @param string[] $attribs
     * @return array
     */
    private function fillData(array $attribs): array
    {
        $data = [
            'manufacturer'      => [],
            'responsibleperson' => [],
        ];
        foreach (\array_keys($data) as $group) {
            foreach ($this->fields as $name) {
                $idx = 'gpsr_' . $group . '_' . $name;
                if (isset($attribs[$idx])) {
                    $data[$group][$name] = $attribs[$idx];
                }
            }
        }

        return $data;
    }

    public function hasData(): bool
    {
        $hasData = false;
        foreach ($this->data as $singleData) {
            if (!empty($singleData['manufacturer']) || !empty($singleData['responsibleperson'])) {
                $hasData = true;
                break;
            }
        }

        return $hasData;
    }

    public function assignData(JTLSmarty $smarty): void
    {
        $smarty->assign('gpsrData', $this->data);
    }
}
