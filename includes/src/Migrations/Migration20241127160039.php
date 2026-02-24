<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20241127160039
 */
class Migration20241127160039 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    public function getDescription(): string
    {
        return 'adds aria label language vars';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'aria',
            'close',
            'Fenster schließen'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'close',
            'close modal'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'toggleNavigation',
            'Menü ausklappbar'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'toggleNavigation',
            'toggle navigation'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'prev',
            'zurück'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'prev',
            'previous'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'next',
            'weiter'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'next',
            'next'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'imageZoom',
            'Bild vergrößern'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'imageZoom',
            'image zoom'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'productlistNavigation',
            'Artikelliste Seitennavigation'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'productlistNavigation',
            'Productlist Navigation'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'numberProductsShown',
            'Anzahl angezeigter Artikel'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'numberProductsShown',
            'number of products shown'
        );

        $this->setLocalization(
            'ger',
            'aria',
            'pagination',
            'pagination'
        );
        $this->setLocalization(
            'eng',
            'aria',
            'pagination',
            'pagination'
        );

        $this->setLocalization(
            'ger',
            'rma',
            'selectProductForReturn',
            'Dieses Produkt zur Retoure hinzufügen'
        );
        $this->setLocalization(
            'eng',
            'rma',
            'selectProductForReturn',
            'add this product to your return'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeLocalization('close', 'aria');
        $this->removeLocalization('toggleNavigation', 'aria');
        $this->removeLocalization('prev', 'aria');
        $this->removeLocalization('next', 'aria');
        $this->removeLocalization('imageZoom', 'aria');
        $this->removeLocalization('numberProductsShown', 'aria');
        $this->removeLocalization('pagination', 'aria');
        $this->removeLocalization('selectProductForReturn', 'rma');
    }
}
