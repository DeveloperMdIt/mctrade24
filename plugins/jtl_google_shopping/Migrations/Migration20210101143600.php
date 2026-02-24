<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Migrations;

use JTL\Filesystem\Filesystem;
use JTL\Plugin\Migration;
use JTL\Shop;
use JTL\Update\IMigration;
use Throwable;

/**
 * Class Migration20210101143600
 * @package Plugin\jtl_google_shopping\Migrations
 */
class Migration20210101143600 extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $fs = Shop::Container()->get(Filesystem::class);
        /** @var Filesystem $fs */
        $base = \PLUGIN_DIR . 'jtl_google_shopping/';
        try {
            if ($fs->fileExists($base . 'src/Backend/Installer.php')) {
                $fs->deleteDirectory($base . 'src/');
            }
            if ($fs->fileExists($base . 'adminmenu/custom_attributes.php')) {
                $fs->delete($base . 'adminmenu/custom_attributes.php');
            }
            if ($fs->fileExists($base . 'adminmenu/custom_exports.php')) {
                $fs->delete($base . 'adminmenu/custom_exports.php');
            }
            if ($fs->fileExists($base . 'adminmenu/custom_mapping.php')) {
                $fs->delete($base . 'adminmenu/custom_mapping.php');
            }
        } catch (Throwable) {
        }
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
    }
}
