<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241203121445
 */
class Migration20241203121445 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    public function getDescription(): string
    {
        return 'add skip-to link language vars';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'global',
            'skipToContent',
            'Zum Hauptinhalt springen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'skipToContent',
            'Skip to main content'
        );

        $this->setLocalization(
            'ger',
            'global',
            'skipToSearch',
            'Zur Suche springen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'skipToSearch',
            'Skip to search'
        );

        $this->setLocalization(
            'ger',
            'global',
            'skipToNav',
            'Zum Menü springen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'skipToNav',
            'Skip to navigation'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('skipToContent', 'global');
        $this->removeLocalization('skipToSearch', 'global');
        $this->removeLocalization('skipToNav', 'global');
    }
}
