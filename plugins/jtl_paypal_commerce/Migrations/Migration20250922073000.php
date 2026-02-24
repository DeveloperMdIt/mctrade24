<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use DateTime;
use JTL\DB\DbInterface;
use JTL\Plugin\Migration;
use JTL\Shop;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\CryptedTagConfigValueHandler;

/**
 * Class Migration20250922073000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250922073000 extends Migration implements IMigration
{
    private CryptedTagConfigValueHandler $cryptedValueHandler;

    public function __construct(DbInterface $db, ?string $info = null, ?DateTime $executed = null)
    {
        parent::__construct($db, $info, $executed);

        $this->cryptedValueHandler = new CryptedTagConfigValueHandler(Shop::Container()->getCryptoService());
    }

    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Add new column for vault_id-hash';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                DROP COLUMN vault_customer_hash'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                ADD COLUMN vault_id_hash VARCHAR(64) NOT NULL DEFAULT \'\''
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                DROP INDEX idx_vault_id'
        );

        $allVaults = $this->db->getCollection(
            'SELECT id, vault_id
                FROM xplugin_jtl_paypal_checkout_vaulting'
        );

        foreach ($allVaults as $vault) {
            $idVault = $vault->vault_id;
            if (\strcmp($idVault ?? '', $this->cryptedValueHandler->prepare($idVault ?? '')) !== 0) {
                $idVault = $this->cryptedValueHandler->getValue($idVault ?? '');
            }
            $this->db->update('xplugin_jtl_paypal_checkout_vaulting', 'id', $vault->id, (object)[
                'vault_id_hash' => \sha1($idVault)
            ]);
        }

        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting ADD UNIQUE INDEX idx_vault_id(vault_id_hash)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                DROP COLUMN vault_id_hash'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting
                ADD COLUMN vault_customer_hash VARCHAR(64) NOT NULL DEFAULT \'\''
        );

        $allVaults = $this->db->getCollection(
            'SELECT id, vault_customer
                FROM xplugin_jtl_paypal_checkout_vaulting'
        );
        foreach ($allVaults as $vault) {
            $customerVault = $vault->vault_customer;
            if (\strcmp($customerVault ?? '', $this->cryptedValueHandler->prepare($customerVault ?? '')) !== 0) {
                $customerVault = $this->cryptedValueHandler->getValue($customerVault ?? '');
            }
            $this->db->update('xplugin_jtl_paypal_checkout_vaulting', 'id', $vault->id, (object)[
                'vault_customer_hash' => \md5($customerVault)
            ]);
        }

        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_vaulting ADD UNIQUE INDEX idx_vault_customer(vault_customer_hash)'
        );
    }
}
