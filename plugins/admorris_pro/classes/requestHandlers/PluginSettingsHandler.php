<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\requestHandlers; use Plugin\admorris_pro\PluginSettings; use Shop; class PluginSettingsHandler { public function load() { $data = (new PluginSettings())->load(); return $data; } }