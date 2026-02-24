<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro_installer\Traits; use JTL\IO\IOError; trait IoRequestTrait { public function request($data, $requestType) { try { if (!$data) { goto X_dSC; } return $this->{$requestType}($data); X_dSC: return $this->{$requestType}(); } catch (\Exception $e) { return new IOError("\105\162\x72\157\162\x20\151\156\40" . get_called_class() . "\40\x63\141\154\154\151\x6e\x67\x20" . $requestType . "\73", 500, [(string) $e]); } } }