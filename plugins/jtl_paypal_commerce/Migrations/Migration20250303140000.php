<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Helper;
use JTL\Plugin\Migration;
use JTL\Shop;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\CryptedTagConfigValueHandler;

/**
 * Class Migration20250303140000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250303140000 extends Migration implements IMigration
{
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Restructure vaulting table';
    }

    /**
     * @inheritDoc
     * @noinspection SqlWithoutWhere
     * @noinspection SqlResolve
     */
    public function up(): void
    {
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                ADD COLUMN vault_customer_hash VARCHAR(64) NOT NULL DEFAULT \'\''
        );
        $this->execute(
            'UPDATE xplugin_jtl_paypal_checkout_vaulting
                SET vault_customer_hash = md5(vault_customer)'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting DROP INDEX idx_vault_customer'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting ADD UNIQUE INDEX idx_vault_customer(vault_customer_hash)'
        );
    }

    /**
     * @inheritDoc
     * @noinspection SqlResolve
     */
    public function down(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        $cryptoService       = Shop::Container()->getCryptoService();
        $db                  = $this->getDB();
        $cryptedValueHandler = new CryptedTagConfigValueHandler($cryptoService);
        $vaults              = $this->getDB()->getObjects(
            'SELECT id, vault_id, vault_customer FROM xplugin_jtl_paypal_checkout_vaulting'
        );
        foreach ($vaults as $vault) {
            $upd = [];
            if (\strcmp($vault->vault_id, $cryptedValueHandler->prepare($vault->vault_id)) !== 0) {
                $upd['vault_id'] = $cryptedValueHandler->getValue($vault->vault_id);
            }
            if (\strcmp($vault->vault_customer, $cryptedValueHandler->prepare($vault->vault_customer)) !== 0) {
                $upd['vault_customer'] = $cryptedValueHandler->getValue($vault->vault_customer);
            }
            if (!empty($upd)) {
                $db->update('xplugin_jtl_paypal_checkout_vaulting', 'id', $vault->id, (object)$upd);
            }
        }

        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting DROP INDEX idx_vault_customer'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting ADD UNIQUE INDEX idx_vault_customer(vault_customer)'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                DROP COLUMN vault_customer_hash'
        );
    }
}
