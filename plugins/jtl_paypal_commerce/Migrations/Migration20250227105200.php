<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Helper;
use JTL\Plugin\Migration;
use JTL\Shop;
use JTL\Update\IMigration;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\ConfigValues;
use Plugin\jtl_paypal_commerce\PPC\CryptedTagConfigValueHandler;

/**
 * Class Migration20250227105200
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250227105200 extends Migration implements IMigration
{
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'Encrypt client credentials';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        $configuration = Configuration::getInstance($plugin, $this->getDB());
        $configuration->deleteConfigItems(['clientID', 'clientSecret']);
        $configValues = \method_exists($configuration, 'getConfigValues')
            ? $configuration->getConfigValues()
            : new ConfigValues($configuration, Shop::Container()->getCryptoService());
        foreach (
            [
                ConfigValues::WORKING_MODE_PRODUCTION,
                ConfigValues::WORKING_MODE_SANDBOX
            ] as $workingMode
        ) {
            $configValues->migrateClientId($workingMode);
            $configValues->migrateClientSecret($workingMode);
        }
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin === null) {
            return;
        }

        $configuration     = Configuration::getInstance($plugin, $this->getDB());
        $configValues      = $configuration->getConfigValues();
        $cryptoService     = Shop::Container()->getCryptoService();
        $cryptedTagHandler = new CryptedTagConfigValueHandler($cryptoService);
        $saveItems         = [];
        foreach (
            [
                ConfigValues::WORKING_MODE_PRODUCTION,
                ConfigValues::WORKING_MODE_SANDBOX
            ] as $workingMode
        ) {
            $idKey        = 'clientID_' . $workingMode;
            $secretKey    = 'clientSecret_' . $workingMode;
            $clientId     = $configuration->getPrefixedConfigItem($idKey, '');
            $clientSecret = $configuration->getPrefixedConfigItem($secretKey, '');

            if (strcmp($clientId, $cryptedTagHandler->prepare($clientId)) !== 0) {
                $saveItems[$idKey] = $cryptedTagHandler->getValue($clientId);
            }
            if (strcmp($clientSecret, $cryptedTagHandler->prepare($clientSecret)) !== 0) {
                $saveItems[$secretKey] = $cryptedTagHandler->getValue($clientSecret);
            }
        }
        $configuration->saveConfigItems($saveItems);
        $configValues->clearAuthToken();
    }
}
