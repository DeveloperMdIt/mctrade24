<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20250311152753
 */
class Migration20250326120500 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'mh';
    }

    public function getDescription(): string
    {
        return 'add lang var for vat id max requests';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'global',
            'ustIDErrorMaxRequests',
            'Der MIAS-Dienst Ihres Landes erhält aktuell zu viele Anfragen. Versuchen Sie es später noch einmal.'
        );
        $this->setLocalization(
            'eng',
            'global',
            'ustIDErrorMaxRequests',
            'Your countrys VIES service is currently receiving too many requests. Please try again later.'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('ustIDErrorMaxRequests', 'global');
    }
}
