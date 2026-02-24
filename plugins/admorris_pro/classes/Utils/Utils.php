<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\Utils; use JTL\Shop; use function Plugin\admorris_pro\isTemplateActive; class Utils { public static function trans($name) { $plugin = Shop::get("\x6f\x70\154\165\x67\x69\156\137\141\144\x6d\x6f\162\x72\151\163\x5f\160\x72\157"); return $plugin->getLocalization()->getTranslation("\141\x64\x6d\x6f\x72\162\x69\163\137\160\162\x6f\x5f" . $name); } public static function isTemplateActive() { return isTemplateActive(); } }