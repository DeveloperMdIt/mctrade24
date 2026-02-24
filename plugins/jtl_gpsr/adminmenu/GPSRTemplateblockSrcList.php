<?php

declare(strict_types=1);

use JTL\Shop;
use Plugin\jtl_gpsr\adminmenu\GPSRTemplateblock;
use Plugin\jtl_gpsr\adminmenu\GPSRTemplatefile;

if (Shop::isFrontend()) {
    return [];
}

return GPSRTemplateblock::getInstance(GPSRTemplatefile::LIST, $this)->getList();
