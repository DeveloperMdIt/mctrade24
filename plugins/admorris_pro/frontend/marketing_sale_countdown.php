<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use Plugin\admorris_pro\AssetLoader; use Plugin\admorris_pro\SaleCountdown; $saleCountdown = new SaleCountdown($oPlugin, $smarty, $assetLoader); $saleCountdown->render();