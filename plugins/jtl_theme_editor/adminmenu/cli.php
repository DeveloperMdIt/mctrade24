<?php declare(strict_types=1);

use JTL\Plugin\Helper;
use JTL\Plugin\State;
use Plugin\jtl_theme_editor\Editor;

ini_set('display_errors', '1');

if (PHP_SAPI !== 'cli') {
    exit;
}

if (!defined('PFAD_ROOT')) {
    require_once dirname(__FILE__, 4) . '/includes/globalinclude.php';
}
$plugin = Helper::getPluginById('jtl_theme_editor');
if ($plugin === null || $plugin->getState() !== State::ACTIVATED) {
    echo 'Plugin could not be loaded. Is it activated?' . PHP_EOL;
    exit(-1);
}

$themesToCompile = null;
if ($argc > 1) {
    $themesToCompile = [];
    for ($i = 1; $i < $argc; ++$i) {
        $themesToCompile[] = $argv[$i];
    }
}
$editor   = new Editor($plugin);
$themes   = $editor->getThemes($themesToCompile);
$count    = count($themes);
$compiled = 0;
$cacheDir = PFAD_ROOT . PFAD_COMPILEDIR . 'jtltpleditortmp/*';
foreach (glob($cacheDir) as $file) {
    unlink($file);
}

foreach ($themes as $_theme) {
    $start = microtime(true);
    printf('%2d / %d %-10s', ++$compiled, $count, $_theme['theme']);
    $result = $editor->compile($_theme['theme'], $_theme['template']);
    $err    = $result['data']['type'] !== 'success';
    $end    = (microtime(true) - $start);
    $msg    = $result['data']['msg'];

    printf("\033[0;%dm[%.2fs]\033[0m %s\n", $err ? 31 : 32, $end, $msg);
}
