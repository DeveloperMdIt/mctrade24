<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\search; class SearchConfigDTO { public function __construct(public readonly string $urlBase, public readonly string $suchIndex, public readonly string $suchServer, public readonly string $suchUser, public readonly string $suchKey) { } }