<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Helper;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\APM;

/**
 * Class Migration20241014143300
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20241014143300 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Remove Auto capture settings for not supported APMs';
    }

    public function up(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        $apmSettings = $this->db->getObjects(
            'SELECT kPlugin, cName
                FROM tplugineinstellungen
                WHERE kPlugin = :pluginID
                    AND cName LIKE \'%_APM_payBeforeOrder\'',
            ['pluginID' => $plugin->getID()]
        );
        foreach ($apmSettings as $apmSetting) {
            if (
                !preg_match('/^jtl_paypal_commerce_([a-zA-Z0-9_-]+)_APM_payBeforeOrder$/', $apmSetting->cName, $hits)
                || \in_array($hits[1], APM::APM_AC)
            ) {
                continue;
            }
            $this->db->delete('tplugineinstellungen', ['kPlugin', 'cName'], [$apmSetting->kPlugin, $apmSetting->cName]);
        }
    }

    public function down(): void
    {
        /* no rollback necessary */
    }
}
