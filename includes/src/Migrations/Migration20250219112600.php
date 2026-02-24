<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;
use Monolog\Level;

class Migration20250219112600 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'fm';
    }

    public function getDescription(): string
    {
        return 'update invalid log level';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $invalid = $this->getDB()->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM teinstellungen
                WHERE cName = \'systemlog_flag\'
                AND cWert NOT IN (' . \implode(',', Level::VALUES) . ')',
            'cnt'
        );
        if ($invalid > 0) {
            $this->execute(
                'UPDATE `teinstellungen` 
                    SET cWert = ' . Level::Error->value
                . ' WHERE cName = \'systemlog_flag\''
            );
        }
        $this->execute(
            'UPDATE `teinstellungen_default` 
                SET cWert = ' . Level::Error->value
            . ' WHERE cName = \'systemlog_flag\''
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
    }
}
