<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use Plugin\admorris_pro\AssetLoader; $javascript = $oPlugin->getPaths()->getFrontendURL() . "\x6a\163\x2f\163\x65\141\162\x63\x68\56\x6a\x73"; $css = $oPlugin->getPaths()->getFrontendURL() . "\143\163\x73\57\x73\145\x61\162\143\x68\x2d\163\x75\x67\x67\145\x73\164\56\143\x73\163"; $assetLoader->addToHead([$javascript, $css], "\x73\145\141\x72\x63\x68");