<?php

declare(strict_types=1);

use JTL\Shop;

return Shop::Container()->getDB()->getObjects(
    'SELECT cISO AS cWert, cNameDeutsch AS cName, cShopStandard, 1 AS nSort 
        FROM tsprache 
        ORDER BY cStandard DESC, cNameDeutsch'
);
