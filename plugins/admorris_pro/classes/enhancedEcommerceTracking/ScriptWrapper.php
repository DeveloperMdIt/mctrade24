<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\enhancedEcommerceTracking; class ScriptWrapper { public function __construct(public string $selector, public string $manipulation, public string $script) { } public function addScript() : void { if (!empty($this->script)) { goto u2rV3; } return; u2rV3: $manipulation = $this->manipulation; \pq($this->selector)->{$manipulation}($this->script); } }