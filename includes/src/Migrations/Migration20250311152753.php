<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20250311152753
 */
class Migration20250311152753 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    public function getDescription(): string
    {
        return 'adds lang vars for better accessibility';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'productDetails',
            'itemInformation',
            'Produkteigenschaft'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'itemInformation',
            'Item information'
        );

        $this->setLocalization(
            'ger',
            'productDetails',
            'itemValue',
            'Wert'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'itemValue',
            'Value'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('itemInformation', 'productDetails');
        $this->removeLocalization('itemValue', 'productDetails');
    }
}
