<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20250326134002
 */
class Migration20250326134002 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'devin';
    }

    public function getDescription(): string
    {
        return 'adds lang vars for ARIA accessibility attributes';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'productOverview',
            'productActions',
            'Produktaktionen'
        );
        $this->setLocalization(
            'eng',
            'productOverview',
            'productActions',
            'Product actions'
        );
        $this->setLocalization(
            'ger',
            'global',
            'clearSearch',
            'Suche löschen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'clearSearch',
            'Clear search'
        );
        $this->setLocalization(
            'ger',
            'global',
            'shopNavigation',
            'Shop-Navigation'
        );
        $this->setLocalization(
            'eng',
            'global',
            'shopNavigation',
            'Shop navigation'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('productActions', 'productOverview');
        $this->removeLocalization('clearSearch', 'global');
        $this->removeLocalization('shopNavigation', 'global');
    }
}
