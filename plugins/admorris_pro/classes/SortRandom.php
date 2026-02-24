<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 declare (strict_types=1); namespace Plugin\admorris_pro; use JTL\Filter\ProductFilter; use JTL\Filter\SortingOptions\AbstractSortingOption; use JTL\Shop; class SortRandom extends AbstractSortingOption { public function __construct(ProductFilter $productFilter) { parent::__construct($productFilter); $admorrisProPlugin = Shop::get("\157\x70\154\x75\147\x69\x6e\x5f\141\144\x6d\x6f\162\x72\x69\x73\x5f\x70\162\157"); $this->orderBy = "\x52\101\x4e\104\50\x29"; $this->setName($admorrisProPlugin->getLocalization()->getTranslation("\x61\144\155\157\162\x72\151\163\x5f\x70\x72\157\x5f\x73\157\x72\x74\137\x72\x61\x6e\144\x6f\x6d")); $this->setPriority(1); $this->setValue(99); } }