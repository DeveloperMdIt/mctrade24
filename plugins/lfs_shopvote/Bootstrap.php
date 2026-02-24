<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote;

use Exception;
use JTL\Alert\Alert;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\DBMigrationHelper;
use Plugin\lfs_shopvote\classes\LfsShopvote;

/**
 * Class Bootstrap
 * @package Plugin\lfs_shopvote
 */
class Bootstrap extends Bootstrapper
{
    public const CRON_ID = 'ShopvoteCron';

    /**
     *
     */
    private const CONSENT_ITEM_ID = 'lfs_shopvote';

    /**
     * @var LfsShopvote
     */
    private ?LfsShopvote $lfsShopVote = null;

    /**
     * @return LfsShopvote
     */
    protected function loadCore()
    {
        if (!$this->lfsShopVote) {
            $this->lfsShopVote = new LfsShopvote($this->getPlugin(), $this->getDB());
        }

        return $this->lfsShopVote;
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher)
    {
        #$this->loadCore();

        $dispatcher->listen(Event::GET_AVAILABLE_CRONJOBS, static function (array &$args) {
            if (!in_array(self::CRON_ID, $args['jobs'], true)) {
                $args['jobs'][] = self::CRON_ID;
            }
        });

        $dispatcher->listen(Event::MAP_CRONJOB_TYPE, static function (array &$args) {
            if ($args['type'] === self::CRON_ID) {
                $args['mapping'] = LfsShopvoteCron::class;
            }
        });

        $dispatcher->listen('shop.hook.' . \HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, function ($args) {
            $consent_state = Shop::Container()->getConsentManager()->hasConsent(self::CONSENT_ITEM_ID);

            if ($consent_state === true || (int)$this->getPlugin()->getConfig()->getValue('sv_respect_jtlconsentmanager') === 0) {
                $this->hook75($args);
            }
        });

        $dispatcher->listen('shop.hook.' . \HOOK_LETZTERINCLUDE_INC, function () {
            $consent_state = Shop::Container()->getConsentManager()->hasConsent(self::CONSENT_ITEM_ID);

            if ($consent_state === true || (int)$this->getPlugin()->getConfig()->getValue('sv_respect_jtlconsentmanager') === 0) {
                $this->hook99();
            }
        });

        $dispatcher->listen('shop.hook.' . \HOOK_ARTIKEL_PAGE, function ($args) {
            $consent_state = Shop::Container()->getConsentManager()->hasConsent(self::CONSENT_ITEM_ID);

            if ($consent_state === true || (int)$this->getPlugin()->getConfig()->getValue('sv_respect_jtlconsentmanager') === 0) {
                $this->hook1($args);
            }
        });

        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function () {
            $consent_state = Shop::Container()->getConsentManager()->hasConsent(self::CONSENT_ITEM_ID);

            if ($consent_state === true || (int)$this->getPlugin()->getConfig()->getValue('sv_respect_jtlconsentmanager') === 0) {
                $this->hook140();
            }
        });

        parent::boot($dispatcher);
    }

    /**
     * @param string $tabName
     * @param int $menuID
     * @param JTLSmarty $smarty
     * @return string
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $this->loadCore();

        if ($tabName === 'Konfiguration') {
            if (version_compare(APPLICATION_VERSION, '5.2', '>=')) {
                $adminUrl = Shop::getAdminURL() . '/plugin/' . $this->getPlugin()->getID();
            }
            else {
                $adminUrl = Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getPlugin()->getID();
            }

            Shop::Smarty()->assign('adminUrl', $adminUrl);

            $alerts = Shop::Container()->getAlertService();

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['sv_api_key'])) {
                    $tokenValidation = Form::validateToken();

                    if ($tokenValidation !== false) {
                        if ($this->lfsShopVote->saveSettings($_POST)) {
                            $alerts->addAlert(
                                Alert::TYPE_SUCCESS,
                                __('Die Einstellungen wurden erfolgreich gespeichert'),
                                'lfsShopVoteSuccess'
                            );
                        }
                        else {
                            $alerts->addAlert(
                                Alert::TYPE_ERROR,
                                __('Die Einstellungen konnten nicht gespeichert werden'),
                                'lfsShopVoteError'
                            );
                        }
                    }
                    else {
                        $alerts->addAlert(
                            Alert::TYPE_ERROR,
                            __('Der übermittelte Formular-Token war ungültig'),
                            'lfsShopVoteError'
                        );
                    }
                }

                if (isset($_POST['sv_sync_days'])) {
                    $syncDays   = (int) $_POST['sv_sync_days'];
                    $syncResult = $this->lfsShopVote->syncNewReviews($syncDays);

                    if ($syncResult['total'] == 0) {
                        if (!empty($syncResult['errors'])) {
                            $logservice = Shop::Container()->getLogService();

                            $logservice->warning(__("ShopVote Sync Fail:\r\n- %s", implode("\r\n -", $syncResult['errors'])));

                            $alerts->addWarning(
                                sprintf('Synchronisation fehlgeschlagen: %s', implode(' ', $syncResult['errors'])),
                                'lfsShopVoteError'
                            );
                        }
                        else {
                            $alerts->addInfo(
                                __(
                                    'Synchronisation: Keine Bewertungen gefunden innerhalb der letzten %d Tage.',
                                    $syncDays
                                ),
                                'lfsShopVoteSuccess'
                            );
                        }
                    }
                    else {
                        if ($syncResult['success'] > 0) {
                            $alerts->addSuccess(
                                n__(
                                    'Synchronisation erfolgreich: %d Eintrag aktualisiert.',
                                    'Synchronisation erfolgreich: %d Einträge aktualisiert.',
                                    $syncResult['success'],
                                    $syncResult['success']
                                ),
                                'lfsShopVoteSuccess'
                            );
                        }

                        if (count($syncResult['errors']) > 0) {
                            $logservice = Shop::Container()->getLogService();

                            $logservice->warning(__("ShopVote Sync Fail:\r\n- %s", implode("\r\n -", $syncResult['errors'])));

                            $alerts->addWarning(
                                n__(
                                    'Synchronisation fehlgeschlagen: %d Eintrag konnte nicht aktualisiert werden. (Mehr Infos im Log (Warning))',
                                    'Synchronisation fehlgeschlagen: %d Einträge konnten nicht aktualisiert werden. (Mehr Infos im Log (Warning))',
                                    count($syncResult['errors']),
                                    count($syncResult['errors'])
                                ),
                                'lfsShopVoteError'
                            );
                        }
                    }
                }
            }

            $this->lfsShopVote = new LfsShopvote($this->getPlugin(), $this->getDB());

            return $this->lfsShopVote->renderConfigTab();
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }

    public function disabled()
    {
        LfsShopvoteCron::removeLockFile();
    }

    public function installed()
    {
        parent::installed();

        $this->loadCore();

        $logservice = Shop::Container()->getLogService();

        try {
            $this->lfsShopVote->syncNewReviews(365);
        }
        catch (\Exception $e) {
            $logservice->debug($this->getPlugin()->getPluginID() . ': ' . $e->getMessage());
        }

        $this->addCron();
    }

    public function uninstalled(bool $deleteData = true)
    {
        parent::uninstalled($deleteData);

        $this->getDB()->delete('tcron', 'jobType', self::CRON_ID);

        LfsShopvoteCron::removeLockFile();
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion)
    {
        parent::updated($oldVersion, $newVersion);

        DBMigrationHelper::migrateToInnoDButf8('xplugin_lfs_shopvote_article_reviews');
        DBMigrationHelper::migrateToInnoDButf8('xplugin_lfs_shopvote_article_review_items');
        DBMigrationHelper::migrateToInnoDButf8('xplugin_lfs_shopvote_config');

        if (version_compare($oldVersion, '1.0.9', '<')) {
            $this->addCron();
        }
    }

    /**
     * @param $args
     * @return void
     */
    private function hook75($args): void
    {
        $_SESSION['lfs_kWarenkorb_shopvote'] = $args['oBestellung']->kWarenkorb;
        $_SESSION['lfs_kKunde_shopvote']     = $args['oBestellung']->kKunde;
    }

    /**
     * @return void
     */
    private function hook99(): void
    {
        $this->loadCore();

        Shop::Smarty()->assign('shopVoteGraphicsCode', $this->lfsShopVote->returnGraphicsCode());
        Shop::Smarty()->assign('lfsShopVotePlugin', $this->getPlugin());
    }

    /**
     * @param $args
     * @return void
     * @throws \SmartyException
     */
    private function hook1($args): void
    {
        $tmpArticle = new Artikel();
        $article = $args['oArtikel'];

        $tmpArticle->fuelleArtikel($this->lfsShopVote->getIdByArtNr($article->cArtNr));

        Shop::Smarty()->assign('lfsShopVoteReviewMode', $this->lfsShopVote->getPlugin()->getConfig()->getValue('sv_show_productreview'));
        Shop::Smarty()->assign('lfsShopVoteReviewStarsTemplate', $this->getPlugin()->getPaths()->getFrontendPath() . 'template/shopvote/review_stars.tpl');

        if (in_array($this->lfsShopVote->getPlugin()->getConfig()->getValue('sv_show_productreview'), ['R', 'A'])) {
            $settings = Shop::Smarty()->getTemplateVars('Einstellungen');

            $settings['bewertung']['bewertung_anzeigen'] = 'Y';

            Shop::Smarty()->assign('Einstellungen', $settings);

            $reviewData = $this->lfsShopVote->getProductReviews($tmpArticle);

            Shop::Smarty()->assign('review_data', $reviewData);

            if ($this->lfsShopVote->getPlugin()->getConfig()->getValue('sv_show_productreview') === 'A') {
                $lfsShopVoteAverageRating = $this->lfsShopVote->calculateCombinedAverageReviews($article, $reviewData);

                if ((int)$article->Bewertungen->oBewertungGesamt->nAnzahl === 0) {
                    $article->Bewertungen->oBewertungGesamt->nAnzahl += $reviewData->rating_count;
                }

                Shop::Smarty()->assign('lfsShopVoteAverageRating', $lfsShopVoteAverageRating);
            }
            else {
                $article->Bewertungen->oBewertungGesamt->nAnzahl = $reviewData->rating_count;
            }

            Shop::Smarty()->assign('Artikel', $article);

            $reviewContent = Shop::Smarty()->fetch($this->getPlugin()->getPaths()->getFrontendPath() . 'template/shopvote/review_content.tpl');

            Shop::Smarty()->assign('reviewContent', $reviewContent);
        }
    }

    /**
     * @return void
     * @throws \SmartyException
     */
    private function hook140(): void
    {
        if (
            isset($_SESSION['lfs_kWarenkorb_shopvote'], $_SESSION['lfs_kKunde_shopvote'])
            && in_array(Shop::$pageType, [PAGE_BESTELLABSCHLUSS, PAGE_BESTELLSTATUS])
        ) {
            $sv_checkout_Kunde = $this->getDB()->selectSingleRow('tkunde', 'kKunde', (int) $_SESSION['lfs_kKunde_shopvote']);
            $sv_kWarenkorb     = $this->getDB()->selectSingleRow('tbestellung', 'kWarenkorb', (int) $_SESSION['lfs_kWarenkorb_shopvote']);
            $sv_bestellung     = new Bestellung((int) $sv_kWarenkorb->kBestellung);

            $sv_bestellung->fuelleBestellung();

            Shop::Smarty()->assign('lfsShopVotePlugin', $this->getPlugin());
            Shop::Smarty()->assign('sv_shop_url', Shop::getURL());
            Shop::Smarty()->assign('sv_checkout_Kunde', $sv_checkout_Kunde);
            Shop::Smarty()->assign('sv_bestellung', $sv_bestellung);

            $review_integration_content = Shop::Smarty()->fetch($this->getPlugin()->getPaths()->getFrontendPath() . 'template/shopvote/review_integration.tpl');

            pq('body')->append($review_integration_content);
        }
    }

    private function addCron()
    {
        $job            = new \stdClass();
        $job->name      = 'Daily Shopvote review sync';
        $job->jobType   = self::CRON_ID;
        $job->frequency = 24;
        $job->startDate = 'NOW()';
        $job->startTime = '02:00:00';

        $this->getDB()->insert('tcron', $job);
    }

}
