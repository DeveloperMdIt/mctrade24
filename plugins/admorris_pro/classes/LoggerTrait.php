<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use JTL\Shop; use Psr\Log\LoggerInterface; trait LoggerTrait { protected LoggerInterface $log; public function initLogger() { $prefix = $this->loggerPrefix ?? (new \ReflectionClass($this))->getShortName(); $logger = Shop::get("\x61\x64\x6d\120\162\x6f\137\114\x6f\x67\x67\x65\162"); if ($logger instanceof LoggerInterface) { goto BeP55; } $logger = Shop::Container()->getLogService(); BeP55: $this->log = $logger; } }