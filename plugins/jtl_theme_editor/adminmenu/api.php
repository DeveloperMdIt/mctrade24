<?php declare(strict_types=1);

use JTL\Plugin\Helper;
use Plugin\jtl_theme_editor\Editor;

if (!defined('PFAD_ROOT')) {
    require_once __DIR__ . '/../../../admin/includes/admininclude.php';
}
global $plugin;
$editor = new Editor($plugin ?? Helper::getPluginById('jtl_theme_editor'));
if (isset($_REQUEST['action'])) {
    $editor->json($_REQUEST['action']);
} else {
    $editor->showEditor();
}
