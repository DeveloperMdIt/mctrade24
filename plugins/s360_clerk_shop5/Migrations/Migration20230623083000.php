<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\Migrations;

use JTL\DB\ReturnType;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20230623083000 extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $test = $this->getDB()->executeQuery(
            'SHOW COLUMNS FROM xplugin_s360_clerk_shop5_store LIKE "private_key"',
            ReturnType::AFFECTED_ROWS
        );

        if (empty($test)) {
            $this->execute(
                'ALTER TABLE `xplugin_s360_clerk_shop5_store`
                ADD COLUMN `private_key` VARCHAR(255) NULL AFTER `api_key`;'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if ($this->doDeleteData()) {
            $this->execute('ALTER TABLE `xplugin_s360_clerk_shop5_store` DROP COLUMN `private_key`;');
        }
    }
}
