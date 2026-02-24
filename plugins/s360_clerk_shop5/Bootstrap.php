<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5;

use DateTime;
use JTL\Catalog\Currency;
use JTL\Customer\CustomerGroup;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Link\LinkInterface;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\s360_clerk_shop5\src\Controllers\AdminDashboardController;
use Plugin\s360_clerk_shop5\src\Controllers\AdminStoresController;
use Plugin\s360_clerk_shop5\src\Controllers\CronJobController;
use Plugin\s360_clerk_shop5\src\Controllers\FrontendController;
use Plugin\s360_clerk_shop5\src\Export\Cronjob;
use Plugin\s360_clerk_shop5\src\Utils\Config;
use Plugin\s360_clerk_shop5\src\Utils\Logger;
use Plugin\s360_clerk_shop5\src\Utils\Snippets;
use Smarty;
use SmartyException;
use Throwable;

use function Functional\first;

class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        if ($this->getPlugin()->getConfig()->getValue(Config::SETTING_CRON_METHOD) === Config::CRON_MODE_TASK) {
            // Hook Cron Job - this is either done as "pseudo" cron job or as a real cronjob via cli
            $dispatcher->listen(Event::MAP_CRONJOB_TYPE, function (array &$args) {
                if ($args['type'] === Config::CRON_JOB_TYPE_GENERATE) {
                    $args['mapping'] = Cronjob::class;
                }
            });
        } elseif ($this->getPlugin()->getConfig()->getValue(Config::SETTING_CRON_METHOD) === Config::CRON_MODE_SYNC) {
            $dispatcher->listen('shop.hook.' . \HOOK_LASTJOBS_HOLEJOBS, function () {
                // Hook into last job on wawi sync.
                try {
                    $controller = new CronJobController();

                    // only generate feeds that are at least a day old to avoid to much load on the server
                    // -> pseudo variant to only generate feeds every 24h like the cron task
                    $feeds = $controller->getFeeds();
                    foreach ($feeds as $feedIndex => $feed) {
                        if ($feed->getUpdatedAt()->diff(new DateTime())->days >= 1) {
                            $controller->handle($feedIndex);
                            break;
                        }
                    }
                } catch (Throwable $err) {
                    Logger::error(
                        'Error in Hook HOOK_LASTJOBS_HOLEJOBS: ' . $err->getMessage() . ' on line ' . $err->getLine()
                        . ' in file ' . $err->getFile()
                    );
                }
            });
        }

        if (Shop::isFrontend()) {
            $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function ($args) {
                $this->registerSmartyPhpFunctions(Shop::Smarty());

                try {
                    $controller = new FrontendController(
                        $this->getPlugin(),
                        Shop::Smarty(),
                        Shop::Container()->getAlertService()
                    );

                    $controller->handle();
                } catch (Throwable $err) {
                    Logger::error(
                        'Error in Hook HOOK_SMARTY_OUTPUTFILTER: ' . $err->getMessage() . ' on line ' . $err->getLine()
                        . ' in file ' . $err->getFile()
                    );
                }
            });

            $dispatcher->listen('shop.hook.' . \HOOK_LETZTERINCLUDE_INC, function () {
                Shop::Smarty()->assign('s360_clerk_snippets', new Snippets(Shop::Smarty(), Shop::Lang()));
            });

            return;
        }

        // Register on events for the admin on the Dispatcher
        $dispatcher->listen('shop.hook.' . \HOOK_PLUGIN_SAVE_OPTIONS, function (array $args) {
            if ($args['hasError']) {
                return;
            }

            $config = new Config();

            // enable cron task
            if ($this->getPlugin()->getConfig()->getValue(Config::SETTING_CRON_METHOD) === Config::CRON_MODE_TASK) {
                $config->enableCronjobTask();
                return;
            }

            // disable cron task
            $config->disableCronjobTask();
        });
    }

    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        try {
            switch ($tabName) {
                case AdminStoresController::TABNAME:
                    $controller = new AdminStoresController(
                        $this->getPlugin(),
                        Shop::Smarty(),
                        Shop::Container()->getAlertService()
                    );
                    return $controller->handle();
                case AdminDashboardController::TABNAME:
                    $controller = new AdminDashboardController(
                        $this->getPlugin(),
                        Shop::Smarty(),
                        Shop::Container()->getAlertService()
                    );
                    return $controller->handle();
                default:
                    return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
            }
        } catch (Throwable $th) {
            Logger::error('Error in ' . $tabName . ': ' .  print_r($th, true));
            return $th->getMessage();
        }
    }

    public function prepareFrontend(LinkInterface $link, JTLSmarty $smarty): bool
    {
        if ($link->getIdentifier() === Config::PAGE_SEARCH_RESULTS) {
            try {
                $controller = new FrontendController(
                    $this->getPlugin(),
                    $smarty,
                    Shop::Container()->getAlertService()
                );

                $controller->handleSearchResults($link);
            } catch (Throwable $err) {
                Logger::error(
                    'Error on clerk search results page: ' . $err->getMessage() . ' on line ' . $err->getLine()
                    . ' in file ' . $err->getFile()
                );
            }
        }

        parent::prepareFrontend($link, $smarty);
        return true;
    }

    public function installed()
    {
        $config = new Config();
        $config->enableCronjobTask();
        $this->createDefaultFeeds();
        $this->clearShopCaches();
        $this->disableFrontend(); // disable Frontend settings on first install
    }

    public function updated($oldVersion, $newVersion)
    {
        $this->clearShopCaches();
    }

    private function clearShopCaches(): void
    {
        Shop::Container()->getCache()->flushTags([
            CACHING_GROUP_LANGUAGE,
            CACHING_GROUP_LICENSES,
            CACHING_GROUP_PLUGIN,
        ]);
    }

    private function createDefaultFeeds(): void
    {
        $lang = first(LanguageHelper::getAllLanguages(), static fn(LanguageModel $lang) => $lang->isDefault());
        $group = first(
            CustomerGroup::getGroups(),
            static fn(CustomerGroup $customerGroup) => $customerGroup->isDefault()
        );

        $id = $this->getDB()->insert('xplugin_s360_clerk_shop5_store', (object) [
            'lang_id' => $lang->getId(),
            'customer_group' => $group->getID(),
        ]);

        // Save settings
        $currency = (new Currency())->getDefault();
        $settings = [
            'enable_products' => true,
            'enable_characteristics' => true,
            'enable_attributes' => true,
            'enable_categories' => true,
            'enable_customers' => true,
            'enable_last_orders' => true,
            'category_separator' => ' > ',
            'currency' => $currency->getID()
        ];

        foreach ($settings as $key => $value) {
            $this->getDB()->insert('xplugin_s360_clerk_shop5_store_settings', (object) [
                'store_id' => $id,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /**
     * Disable frontend like live search etc to not break the frontend due to unconfigured clerk.
     *
     * The reason we have to do that here and not as a default in the settings is, that if we would set it in the
     * settings we would deactive the frontend for existing merchants on the update -> we do not want that!
     */
    private function disableFrontend()
    {
        $settings = [
            Config::SETTING_LIVESEARCH_SELECTOR,
            Config::SETTING_SEARCHPAGE_ACTIVE,
            Config::SETTING_SHOPPINGCART_ACTIVE,
            Config::SETTING_ARTICLE_SLIDER_ACTIVE,
            Config::SETTING_EXIT_INTENT_ACTIVE,
            Config::SETTING_POWERSTEP_ACTIVE,
        ];

        foreach ($settings as $setting) {
            $this->getDB()->update(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$this->getPlugin()->getID(), $setting],
                (object)['cWert' => '']
            );
        }
    }

    private function registerSmartyPhpFunctions(JTLSmarty $smarty): void
    {
        if (\version_compare(Smarty::SMARTY_VERSION, '4.5', '<')) {
            return;
        }

        // try to register
        try {
            $smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, 'constant', '\constant');
        } catch (SmartyException $e) {
              // probably already registered by different plugin
        }
    }
}
