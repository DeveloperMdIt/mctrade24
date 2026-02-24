<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20250121085237
 */
class Migration20250121085237 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    public function getDescription(): string
    {
        return 'adds lang vars for accessibility';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'news',
            'newsCommentForPosting',
            'Kommentar zum Artikel %s'
        );
        $this->setLocalization(
            'eng',
            'news',
            'newsCommentForPosting',
            'Comment for the news posting %s'
        );
        $this->setLocalization(
            'ger',
            'news',
            'newsCommentsForPosting',
            'Kommentare zum Artikel %s'
        );
        $this->setLocalization(
            'eng',
            'news',
            'newsCommentsForPosting',
            'Comment for the news posting %s'
        );
        $this->setLocalization(
            'ger',
            'productDetails',
            'configTableHeaderProduct',
            'Artikel/Gruppe'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'configTableHeaderProduct',
            'product/group'
        );
        $this->setLocalization(
            'ger',
            'productDetails',
            'configTableHeaderPrice',
            'Preis'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'configTableHeaderPrice',
            'price'
        );
        $this->setLocalization(
            'ger',
            'productDetails',
            'showMoreTabs',
            'weitere Registerkarten anzeigen'
        );
        $this->setLocalization(
            'eng',
            'productDetails',
            'showMoreTabs',
            'show more tabs'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('newsCommentForPosting', 'news');
        $this->removeLocalization('newsCommentsForPosting', 'news');
        $this->removeLocalization('configTableHeaderPrice', 'productDetails');
        $this->removeLocalization('configTableHeaderProduct', 'productDetails');
        $this->removeLocalization('showMoreTabs', 'productDetails');
    }
}
