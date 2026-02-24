<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241022162416
 */
class Migration20241022162416 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    public function getDescription(): string
    {
        return 'add lang var for required input fields';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'global',
            'requiredInfo',
            'Alle mit <span class="required-star">*</span> markierten Felder sind Pflichtfelder.'
        );
        $this->setLocalization(
            'eng',
            'global',
            'requiredInfo',
            'All fields marked with <span class="required-star">*</span> are mandatory.'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('requiredInfo', 'global');
    }
}
