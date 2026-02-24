<?php

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20240708134610
 */
class Migration20240708134610 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'tnt';
    }

    public function getDescription(): string
    {
        return 'shipping_hint_refactoring';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization(
            'ger',
            'basket',
            'noShippingCostsReached',
            'Ihre Bestellung ist mit %s versandkostenfrei nach %s lieferbar.'
        );
        $this->setLocalization(
            'eng',
            'basket',
            'noShippingCostsReached',
            'Your order can be shipped for free with %s to %s.'
        );
        $this->setLocalization(
            'ger',
            'basket',
            'noShippingCostsAt',
            'Noch %s und wir versenden kostenfrei mit %s nach %s.'
        );
        $this->setLocalization(
            'eng',
            'basket',
            'noShippingCostsAt',
            'Another %s and your order will be eligible for free shipping with %s to %s.'
        );
        $this->setLocalization(
            'ger',
            'account data',
            'checkoutSaveShippingAddressAsDefault',
            'Als Standard-Lieferadresse verwenden'
        );
        $this->setLocalization(
            'eng',
            'account data',
            'checkoutSaveShippingAddressAsDefault',
            'Use as default shipping address'
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->setLocalization(
            'ger',
            'basket',
            'noShippingCostsReached',
            'Ihre Bestellung ist mit %s versandkostenfrei %s lieferbar.'
        );
        $this->setLocalization(
            'eng',
            'basket',
            'noShippingCostsReached',
            'Your order can be shipped for free with %s %s.'
        );
        $this->setLocalization(
            'ger',
            'basket',
            'noShippingCostsAt',
            'Noch %s und wir versenden kostenfrei mit %s nach %s.'
        );
        $this->setLocalization(
            'eng',
            'basket',
            'noShippingCostsAt',
            'Another %s and your order will be eligible for free shipping with %s to %s.'
        );
        $this->removeLocalization('checkoutSaveShippingAddressAsDefault', 'account data');
    }
}
