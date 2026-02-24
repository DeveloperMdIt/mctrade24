<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; class Singleton { private static $instances = []; protected function __construct() { } protected function __clone() { } public function __wakeup() { throw new \Exception("\x43\141\156\x6e\x6f\x74\40\x75\x6e\x73\145\162\151\x61\x6c\151\x7a\x65\40\x73\151\156\x67\154\145\x74\x6f\156"); } public static function getInstance() : static { $subclass = static::class; if (isset(self::$instances[$subclass])) { goto f3hwx; } self::$instances[$subclass] = new static(); f3hwx: return self::$instances[$subclass]; } }