<?php

declare(strict_types=1);

use JTL\DB\ReturnType;
use JTL\Shop;

return Shop::Container()->getDB()->query(
    'SELECT tversandart.kVersandart AS cWert, tversandart.cName
        FROM tversandart',
    ReturnType::ARRAY_OF_OBJECTS
);
