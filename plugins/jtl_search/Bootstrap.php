<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use Illuminate\Support\Collection;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Request;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class Bootstrap
 * @package Plugin\jtl_search
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @var Admin|null
     */
    private ?Admin $admin;

    private const SEARCH_CRON_JOB = 'jtl_search_full_export';

    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        $this->validateConfig();
        parent::boot($dispatcher);
        $plugin = $this->getPlugin();
        $dispatcher->listen(Event::MAP_CRONJOB_TYPE, static function (&$args) {
            if ($args['type'] === self::SEARCH_CRON_JOB) {
                $args['mapping'] = ExportJob::class;
            }
        });
        $dispatcher->listen(Event::GET_AVAILABLE_CRONJOBS, function (array &$args) {
            if (isset($args['jobs'])) {
                Shop::Container()->getGetText()->loadPluginLocale('backend', $this->getPlugin());
                $args['jobs'][] = self::SEARCH_CRON_JOB;
            }
        });
        if (!Shop::isFrontend()) {
            require_once $plugin->getPaths()->getBasePath() . 'includes/defines_inc.php';
            $this->admin = new Admin($this->getDB(), Shop::Container()->getBackendLogService());

            return;
        }
        $hooks = new Hooks($plugin, $this->getDB(), Shop::Container()->getLogService());
        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_INC, static function (array $args) use ($plugin) {
            $smarty = $args['smarty'];
            /** @var JTLSmarty $smarty */
            $smarty->assign('jtl_search_frontendURL', $plugin->getPaths()->getFrontendURL())
                ->assign('jtl_search_align', $plugin->getConfig()->getValue('jtlsearch_suggest_align'));
        });
        $listenTo = [
            \HOOK_FILTER_PAGE,
            \HOOK_FILTER_INC_GIBARTIKELKEYS,
            \HOOK_NAVI_PRESUCHE,
            \HOOK_NAVI_SUCHE,
            \HOOK_ARTIKEL_PAGE,
            \HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE,
            \HOOK_ARTIKEL_XML_BEARBEITEINSERT,
            \HOOK_HERSTELLER_XML_BEARBEITEINSERT,
            \HOOK_KATEGORIE_XML_BEARBEITEINSERT,
            \HOOK_HERSTELLER_XML_BEARBEITEDELETES,
            \HOOK_KATEGORIE_XML_BEARBEITEDELETES,
            \HOOK_ARTIKEL_XML_BEARBEITEDELETES,
            \HOOK_LASTJOBS_HOLEJOBS,
            \HOOK_QUICKSYNC_XML_BEARBEITEINSERT,
            \HOOK_SEOCHECK_ANFANG
        ];
        foreach ($listenTo as $hook) {
            $dispatcher->listen('shop.hook.' . $hook, [$hooks, 'exec' . $hook]);
        }
        $dispatcher->listen(Event::MAP_CRONJOB_TYPE, static function (&$args) {
            if ($args['type'] === 'JTLSearchExport') {
                $args['mapping'] = ExportJob::class;
            }
        });
    }

    /**
     *
     */
    private function validateConfig(): void
    {
        $projectID = $this->getPlugin()->getConfig()->getValue('cProjectId');
        if (!empty($projectID)) {
            return;
        }
        $values = $this->getPlugin()->getConfig()->getOptions();
        $data   = $this->getDB()->getObjects('SELECT * FROM tjtlsearchserverdata');
        foreach ($data as $item) {
            if (isset($item->cKey) && \strlen($item->cKey) > 0) {
                $this->updatePluginOption($values, $item->cKey, $item->cValue);
            }
        }
        $this->getCache()->flushTags([$this->getPlugin()->getCache()->getGroup()]);
    }

    /**
     * @inheritdoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $url = \method_exists($this->getPlugin()->getPaths(), 'getBackendURL')
            ? ($this->getPlugin()->getPaths()->getBackendURL())
            : (Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getPlugin()->getID());
        $smarty->assign('pluginID', $this->getPlugin()->getID())
            ->assign('pluginAdminURL', $url);
        if ($tabName === 'Verwaltung') {
            if (($languages = Request::postVar('jtlsearch_export_languages')) !== null) {
                $default     = LanguageHelper::getDefaultLanguage();
                $languages[] = $default->getCode();
                $this->getDB()->query('TRUNCATE TABLE tjtlsearchexportlanguage');
                foreach (\array_unique($languages) as $code) {
                    $this->getDB()->insert('tjtlsearchexportlanguage', (object)['cISO' => $code]);
                }
            }

            return $this->admin->getManageTab($smarty, $this->getPlugin());
        }
        if ($tabName === 'licenceKey') {
            return $this->admin->getTestPeriodTab($smarty, $this->getPlugin());
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }

    /**
     * @param Collection $options
     * @param string     $name
     * @param string     $value
     */
    private function updatePluginOption(Collection $options, string $name, string $value): void
    {
        $hit = $options->first(static function ($e) use ($name) {
            return $e->valueID === $name;
        });
        if ($hit !== null) {
            $hit->value = $value;
            $this->getDB()->update(
                'tplugineinstellungen',
                ['kPlugin', 'cName'],
                [$this->getPlugin()->getID(), $name],
                (object)['cWert' => $value]
            );
        }
        $this->getPlugin()->getConfig()->setOptions($options);
    }

    private function addCron(): void
    {
        $job            = new \stdClass();
        $job->name      = 'JTL Search full export';
        $job->jobType   = self::SEARCH_CRON_JOB;
        $job->frequency = 24;
        $job->startDate = 'NOW()';
        $job->startTime = '00:00:00';
        $this->getDB()->insert('tcron', $job);
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
        if (\version_compare($oldVersion, '1.22.0', '<')) {
            $this->addCron();
        }
        parent::updated($oldVersion, $newVersion);
    }

    /**
     * @inheritdoc
     */
    public function installed(): void
    {
        parent::installed();
        $this->addCron();
    }

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
        parent::uninstalled($deleteData);
        $this->getDB()->delete('tcron', 'jobType', self::SEARCH_CRON_JOB);
    }
}
