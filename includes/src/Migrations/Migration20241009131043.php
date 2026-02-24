<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241009131043
 */
class Migration20241009131043 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'sl';
    }

    public function getDescription(): string
    {
        return 'Set default for min password length to 12';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            "UPDATE teinstellungen_default SET cWERT = '12' WHERE cName = 'kundenregistrierung_passwortlaenge'"
        );
    }

    /**F
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute(
            "UPDATE teinstellungen_default SET cWERT = '8' WHERE cName = 'kundenregistrierung_passwortlaenge'"
        );
    }
}
