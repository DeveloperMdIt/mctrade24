<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Carrier;

/**
 * Class Migration20250521103600
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250521103600 extends Migration implements IMigration
{
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Update carrier mapping';
    }

    /** @var array */
    private array $update = [
        'DE_DHL_EXPRESS' => Carrier::CARRIER_DE_DHL_PACKET,
        'DHL_API'        => Carrier::CARRIER_DE_DHL_PACKET,
        'GLS_DE'         => Carrier::CARRIER_DE_GLS,
        'TNT_DE'         => Carrier::CARRIER_DE_TNT,
    ];

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        foreach ($this->update as $old => $new) {
            $this->getDB()->update('xplugin_jtl_paypal_checkout_carrier_mapping', 'carrier_paypal', $old, (object)[
                'carrier_paypal' => $new,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        foreach (\array_flip($this->update) as $old => $new) {
            $this->getDB()->update('xplugin_jtl_paypal_checkout_carrier_mapping', 'carrier_paypal', $old, (object)[
                'carrier_paypal' => $new,
            ]);
        }
    }
}
