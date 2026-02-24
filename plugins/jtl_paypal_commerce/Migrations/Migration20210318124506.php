<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20210318124506
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20210318124506 extends Migration implements IMigration
{
    /**
     * @var string
     */
    protected $description = 'Remove settings during deinstallation.';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        if ($this->doDeleteData()) {
            $this->getDB()->query(
                "DELETE FROM tplugineinstellungen WHERE cName LIKE 'jtl_paypal_commerce\_%'"
            );
        }
    }
}
