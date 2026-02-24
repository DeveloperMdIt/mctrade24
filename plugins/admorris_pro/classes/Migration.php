<?php
/*   __________________________________________________
    |  Copyright by admorris.pro  |
    |__________________________________________________|
*/
 namespace Plugin\admorris_pro; use DateTime; use JTL\DB\DbInterface; use JTL\Plugin\Migration as PluginMigration; class Migration extends PluginMigration { use LoggerTrait; public function __construct(DbInterface $db, $info = null, ?DateTime $executed = null) { require_once realpath(__DIR__ . "\x2f\x2e\56\x2f\142\x75\x69\154\144\57\x76\145\156\144\157\162\57\163\x63\x6f\x70\x65\x72\x2d\141\165\164\x6f\154\157\141\x64\56\160\150\160"); require_once realpath(__DIR__ . "\57\56\x2e\57\150\x65\x6c\x70\145\162\137\146\x75\156\x63\x74\x69\157\156\x73\x2e\160\x68\160"); require_once realpath(__DIR__ . "\57\56\56\x2f\x68\x65\154\x70\145\x72\163\x2e\160\150\160"); $this->initLogger(); parent::__construct($db, $info, $executed); } }