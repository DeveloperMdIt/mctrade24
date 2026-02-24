<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Helper;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20250109094500
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250109094500 extends Migration implements IMigration
{
    /**
     * @inheritDoc
     */
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return /** @lang text */ 'Create repeated last job for pending payments';
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

        $this->db->insert('tlastjob', (object)[
            'cType'     => 'RPT',
            'nJob'      => $plugin->getID(),
            'cJobName'  => $plugin->getPluginID(),
            'dErstellt' => 'NOW()',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        $this->db->executeQueryPrepared(
            'DELETE FROM tlastjob
                WHERE cType = \'RPT\'
                    AND nJob = :pluginID
                    AND cJobName = :pluginIdentifier',
            [
                'pluginID'         => $plugin->getID(),
                'pluginIdentifier' => $plugin->getPluginID()
            ],
        );
    }
}
