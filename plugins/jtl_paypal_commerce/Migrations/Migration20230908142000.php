<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Carrier;

/**
 * Class Migration20230908142000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20230908142000 extends Migration implements IMigration
{
    /** @var string */
    protected $description = 'Update carrier mapping';

    /** @var array */
    private array $update = [
        'DE_DEUTSCHE'                              => Carrier::CARRIER_DE_POST,
        'DE_DEUTSHCE_POST_DHL_TRACK_TRACE_EXPRESS' => Carrier::CARRIER_DE_DHL_EXPRESS,
        'DE_DHL_ECOMMERCE'                         => Carrier::CARRIER_DE_DHL_PACKET,
        'DE_DHL_PACKET'                            => Carrier::CARRIER_DE_DHL_PACKET,
        'DE_DPD'                                   => Carrier::CARRIER_DE_DPD,
        'DE_GLS'                                   => Carrier::CARRIER_DE_GLS,
        'DE_HERMES'                                => Carrier::CARRIER_DE_HERMES,
        'DE_TNT'                                   => Carrier::CARRIER_DE_TNT,
        'DE_UPS'                                   => Carrier::CARRIER_DE_UPS,
        'DE_FEDEX'                                 => Carrier::CARRIER_FEDEX,
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
