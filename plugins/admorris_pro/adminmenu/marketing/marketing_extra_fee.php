<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use JTL\Shop; function admExtraFeeGetTaxOptions() { $data = Shop::Container()->getDB()->query("\x53\x45\x4c\x45\x43\124\40\52\x20\x46\122\x4f\x4d\40\x74\x73\x74\x65\165\x65\162\x6b\154\x61\163\163\145", 2); $response = json_encode($data); header("\x43\x6f\156\164\x65\156\x74\x2d\x74\171\x70\x65\x3a\40\141\160\x70\x6c\x69\143\141\x74\151\157\156\57\x6a\163\157\x6e"); return $response; }