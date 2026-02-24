<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro\ioHandling; use JTL\IO\IOError; trait IoRequestTrait { public function request($data, $requestType) { try { if (!$data) { goto joNJe; } return $this->{$requestType}($data); joNJe: return $this->{$requestType}(); } catch (\Exception $e) { return new IOError("\x45\162\162\157\x72\x20\151\x6e\40" . get_called_class() . "\40\x63\x61\x6c\x6c\x69\x6e\x67\40" . $requestType . "\73", 500, [(string) $e]); } } }