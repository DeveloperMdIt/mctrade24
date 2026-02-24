<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241203145348
 */
class Migration20241203145348 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'dr';
    }

    public function getDescription(): string
    {
        return 'add google mail method setting';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $kEinstellungenConf = $this->fetchOne(
            "SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'email_methode'"
        )->kEinstellungenConf;

        $this->execute(
            'INSERT INTO teinstellungenconfwerte (kEinstellungenConf, cName, cWert, nSort)
            VALUE (' . $kEinstellungenConf . ", 'Google Mail via OAuth', 'gmail', 6)"
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $kEinstellungenConf = $this->fetchOne(
            "SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName = 'email_methode'"
        )->kEinstellungenConf;

        $this->execute(
            'DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf = ' . $kEinstellungenConf
            . " AND cWert = 'gmail'"
        );
    }
}
