<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Catalog\Currency;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;

class Helpers
{
    public function __construct(private PluginInterface $plugin)
    {
    }

    public function getFeedFilePath(StoreEntity $feed): string
    {
        return $this->plugin->getPaths()->getBasePath() . 'export' . DIRECTORY_SEPARATOR . $feed->getHash() . '.json';
    }

    public function getFeedUrl(StoreEntity $feed): string
    {
        return $this->plugin->getPaths()->getFrontendURL() . 'feed/' . $feed->getHash();
    }

    /**
     * JTL 5.1 backwards compatible Currency::loadAll
     * @return Currency[]
     */
    public function loadAllCurrencies()
    {
        $currencies = [];
        foreach (Shop::Container()->getDB()->selectAll('twaehrung', [], []) as $item) {
            $currencies[] = new Currency((int) $item->kWaehrung);
        }

        return $currencies;
    }

    public function getFullAdminTabUrl(string $tabname, array $queryParts = []): string
    {
        $tab = $this->plugin->getAdminMenu()->getItems()->first(
            static fn ($item) => $item->cName && $item->cName == $tabname
        );

        // In Shop 5.2 and newer this is .../plugin/PLUGIN_ID
        $url = '%s/admin/plugin/' . $this->plugin->getID() . '?%s#plugin-tab-%s';

        if (version_compare(APPLICATION_VERSION, '5.2.0-beta', '<')) {
            $queryParts = ['kPlugin' => $this->plugin->getID()] + $queryParts;
            $url = '%s/admin/plugin.php?%s#plugin-tab-%s'; // Before Shop 5.2 this is .../plugin.php?kPlugin=PLUGIN_ID
        }

        return sprintf(
            $url,
            Shop::getURL(true),
            http_build_query($queryParts),
            $tab ? $tab->kPluginAdminMenu : ''
        );
    }
    
    public function getClerkVerifyApiUrl(): string
    {
        return 'https://api.clerk.io/v2/token/verify';
    }
}
