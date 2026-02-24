<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; class TemplateSettingsDTO { public function __construct(public ?object $templateCustom = null, public ?object $template = null, public ?array $themeVars = null) { } }