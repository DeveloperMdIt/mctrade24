<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Helper;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\APM;

/**
 * Class Migration20250624150600
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250624150600 extends Migration implements IMigration
{
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Enable Auto-Capture for all APMs';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        foreach (APM::APM_AC as $apmAC) {
            $this->db->delete(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$plugin->getID(), 'jtl_paypal_commerce_' . $apmAC . '_APM_payBeforeOrder']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        // undo not possible
    }
}
