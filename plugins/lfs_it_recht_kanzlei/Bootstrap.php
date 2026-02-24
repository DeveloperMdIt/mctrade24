<?php declare(strict_types=1);

namespace Plugin\lfs_it_recht_kanzlei;

use JTL\Alert\Alert;
use JTL\DB\ReturnType;
use JTL\Events\Dispatcher;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Update\DBMigrationHelper;
use Plugin\lfs_it_recht_kanzlei\classes\ItrkConnector;

/**
 * Class Bootstrap
 * @package Plugin\lfs_it_recht_kanzlei
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        if (Request::getInt('agws_it_recht_kanzlei') === 1 || Request::getInt('lfs_it_recht_kanzlei') === 1) {
            $dispatcher->listen('shop.hook.' . \HOOK_INDEX_NAVI_HEAD_POSTGET, function ($args) {
                $this->hook132($_POST);
            });
        }
    }

    /**
     * @param string $tabName
     * @param int $menuID
     * @param JTLSmarty $smarty
     * @return string
     * @throws \SmartyException
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        if (\version_compare(APPLICATION_VERSION, '5.2', '>=')) {
            $currentUrl = Shop::getAdminURL() . '/plugin/' . $this->getPlugin()->getID();
            $adminSeo = true;
        }
        else {
            $currentUrl = Shop::getAdminURL() . '/plugin.php?kPlugin=' . $this->getPlugin()->getID();
            $adminSeo = false;
        }

        $lfs_it_recht_kanzlei_log_stepAction = (isset($_GET['action'])) ? \htmlspecialchars($_GET['action']) : '';

        if ($lfs_it_recht_kanzlei_log_stepAction === 'delete') {
            $this->getDB()->executeQuery('DELETE FROM xplugin_lfs_it_recht_kanzlei_tLog', ReturnType::AFFECTED_ROWS);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, 'Datensätze wurden gelöscht!', 'lfsItrkSuccess');
            $lfs_it_recht_kanzlei_log_cAnzahl = 0;
        }

        if ($tabName === 'Rechtstexte-Log') {
            $lfs_it_recht_kanzlei_log_arr = $this->getDB()->query('SELECT xplugin_lfs_it_recht_kanzlei_tLog.*
                                                  FROM xplugin_lfs_it_recht_kanzlei_tLog
                                                  ORDER BY kUpdate DESC', ReturnType::ARRAY_OF_OBJECTS);


            $lfs_it_recht_kanzlei_log_cAnzahl    = count($lfs_it_recht_kanzlei_log_arr);

            if ($lfs_it_recht_kanzlei_log_stepAction === 'refresh') {
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, 'Anzeige wurde aktualisiert', 'lfsItrkSuccess');
            }

            return $smarty->assign('lfsPlugin', $this->getPlugin())
                ->assign('lfs_it_recht_kanzlei_log_cAnzahl', $lfs_it_recht_kanzlei_log_cAnzahl)
                ->assign('lfs_it_recht_kanzlei_log_arr', $lfs_it_recht_kanzlei_log_arr)
                ->assign('adminUrl', $currentUrl)
                ->assign('adminSeo', $adminSeo)
                ->fetch($this->getPlugin()->getPaths()->getAdminPath() . 'template/lfs_it_recht_kanzlei_log.tpl');
        }

        if ($tabName === 'Einstellungen')
        {
            $itrkConnector = new ItrkConnector($this->getPlugin(), $this->getDB());
            $stepPlugin    = 'einstellung';
            if (isset($_POST['stepPlugin']) && $_POST['stepPlugin'] == $stepPlugin && !isset($_POST['syncGroupSettings'])) {
                $tokenValidation = Form::validateToken();
                if ($tokenValidation !== false) {
                    $itrkConnector->updateSettings($_POST);
                }
                else {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, 'Der Sicherheitstoken konnte nicht validiert werden!',
                        'lfsItrkError');
                }
            }

            $einstellungen_arr = $this->getDB()->executeQueryPrepared('SELECT * FROM tplugineinstellungen WHERE kPlugin = :kPlugin',
                ['kPlugin' => $this->getPlugin()->getID()],
                ReturnType::ARRAY_OF_ASSOC_ARRAYS
            );

            foreach ($einstellungen_arr as $einstellung_arr) {
                if ($einstellung_arr['cName'] === 'activeAttachments')
                {
                    $cEinstellungen_arr[$einstellung_arr['cName']] = explode(",", $einstellung_arr['cWert']);
                }
                else
                {
                    $cEinstellungen_arr[$einstellung_arr['cName']] = $einstellung_arr['cWert'];
                }
            }

            $lfs_it_recht_kanzlei_sprache = $this->getDB()->query("SELECT kSprache, cISO FROM tsprache WHERE cShopStandard = 'Y'", 1);

            return $smarty->assign('bPDFDirStatus', \is_writable($this->getPlugin()->getPaths()->getBasePath() . 'pdf-dokumente/'))
                ->assign('bPDFDir_Mail_Status', \is_writable(\PFAD_ROOT . 'admin/includes/emailpdfs'))
                ->assign('cEinstellungen_arr', $cEinstellungen_arr)
                ->assign('checkStatus_agb',
                    $itrkConnector->check_spezialseite('agb', $lfs_it_recht_kanzlei_sprache->kSprache))
                ->assign('checkStatus_widerruf',
                    $itrkConnector->check_spezialseite('widerruf', Shop::Lang()->currentLanguageID))
                ->assign('checkStatus_impressum',
                    $itrkConnector->check_spezialseite('impressum', Shop::Lang()->currentLanguageID))
                ->assign('checkStatus_datenschutz',
                    $itrkConnector->check_spezialseite('datenschutz', Shop::Lang()->currentLanguageID))
                ->assign('cAPIUrl', Shop::getURL())
                ->assign('cPDFDir', $this->getPlugin()->getPaths()->getBasePath() . 'pdf-dokumente/')
                ->assign('cPDFDir_Mail', \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS)
                ->assign('stepPlugin', $stepPlugin)
                ->assign('oPlugin', $this->getPlugin())
                ->fetch($this->getPlugin()->getPaths()->getAdminPath() . 'template/lfs_it_recht_kanzlei_einstellungen.tpl');
        }

        if ($tabName === 'Kundengruppen-Einstellungen')
        {
            $stepPlugin = 'syncsettings';
            $itrkConnector = new ItrkConnector($this->getPlugin(), $this->getDB());

            $syncSettingsTab = $this->getDB()->selectSingleRow('tpluginadminmenu', 'kPlugin', $this->getPlugin()->getID(),
                'cDateiname', 'lfs_it_recht_kanzlei_syncsettings.php');

            if (isset($_POST['stepPlugin'], $_POST['syncGroupSettings'])) {
                $tokenValidation = Form::validateToken();
                if ($tokenValidation !== false) {
                    $itrkConnector->setSyncSettings($_POST['activeGroups']);
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, 'Die Kundengruppen-Einstellungen wurden gespeichert!', 'lfsItrkSuccess');
                }
                else {
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, 'Der Sicherheitstoken konnte nicht validiert werden!', 'lfsItrkError');
                }
            }

            $lfs_it_recht_kanzlei_sprache = $this->getDB()->selectSingleRow('tsprache', 'cShopStandard', 'Y');
            $oKundengruppen_arr = $this->getDB()->executeQuery('SELECT * FROM tkundengruppe', ReturnType::ARRAY_OF_OBJECTS);
            $oSyncblockKundengruppen_arr = $this->getDB()->executeQuery('SELECT kKundengruppe 
                                                            FROM xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock',
                ReturnType::ARRAY_OF_OBJECTS);

            $oBlockedGroup_ids= [];

            if (is_array($oSyncblockKundengruppen_arr) && count($oSyncblockKundengruppen_arr)>0) {
                foreach ($oSyncblockKundengruppen_arr as $oGroup) {
                    $oBlockedGroup_ids[] = $oGroup->kKundengruppe;
                }
            }

            return $smarty->assign('cPluginTab', $this->getPlugin()->getAdminMenu()->getItems()->where('name', 'Kundengruppen-Einstellungen')->first()->kPluginAdminMenu)
                ->assign('stepPlugin', $stepPlugin)
                ->assign('oSyncblockKundengruppen_arr', $oSyncblockKundengruppen_arr)
                ->assign('oBlockedGroup_ids', $oBlockedGroup_ids)
                ->assign('oKundengruppen_arr', $oKundengruppen_arr)
                ->assign('oPlugin', $this->getPlugin())
                ->fetch($this->getPlugin()->getPaths()->getAdminPath() . 'template/lfs_it_recht_kanzlei_syncsettings.tpl');
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }

    /**
     * @return mixed|void
     */
    public function installed()
    {
        $init_APIToken = \md5(\uniqid('', true));

        $this->getDB()->executeQueryPrepared('INSERT INTO tplugineinstellungen (kPlugin, cName, cWert) VALUES
				(:kPlugin, "cAPIToken", :apiToken),
				(:kPlugin, "cPDFMail", "1"),
				(:kPlugin, "cPDFDown", "1"),
                (:kPlugin, "saveDseContentAs", "legaltext"),
                (:kPlugin, "activeAttachments", "agb,wrb,dse")',
            [
                'kPlugin' => $this->getPlugin()->getID(),
                'apiToken' => $init_APIToken
            ],
            ReturnType::DEFAULT
        );

        if (Shop::Container()->getCache()->isActive()) {
            Shop::Container()->getCache()->flush($this->getPlugin()->getCache()->getGroup());
        }

        parent::installed();
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion)
    {
        $tmpSetting = $this->getDB()->executeQueryPrepared("SELECT * FROM tplugineinstellungen
                                                            WHERE 
                                                                kPlugin = :kPlugin 
                                                                AND  
                                                                cName = :cName",
        [
            'kPlugin' => $this->getPlugin()->getID(),
            'cName' => 'saveDseContentAs'
        ],
        ReturnType::SINGLE_OBJECT);

        if (!is_object($tmpSetting))
        {
            $this->getDB()->executeQueryPrepared('INSERT INTO tplugineinstellungen (kPlugin, cName, cWert) VALUES
				(:kPlugin, "saveDseContentAs", "content")',
                [
                    'kPlugin' => $this->getPlugin()->getID(),
                ],
                ReturnType::DEFAULT
            );
        }

        DBMigrationHelper::migrateToInnoDButf8('xplugin_lfs_it_recht_kanzlei_tLog');
        DBMigrationHelper::migrateToInnoDButf8('xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock');

        if ((string)$newVersion === '1.0.5')
        {
            if (file_exists($this->getPlugin()->getPaths()->getAdminPath() . 'lfs_it_recht_kanzlei_einstellungen.php'))
            {
                @unlink($this->getPlugin()->getPaths()->getAdminPath() . 'lfs_it_recht_kanzlei_einstellungen.php');
            }

            if (file_exists($this->getPlugin()->getPaths()->getAdminPath() . 'lfs_it_recht_kanzlei_syncsettings.php'))
            {
                @unlink($this->getPlugin()->getPaths()->getAdminPath() . 'lfs_it_recht_kanzlei_syncsettings.php');
            }
        }

        if ((string)$newVersion === '1.0.7')
        {
            $this->getDB()->executeQueryPrepared('INSERT INTO tplugineinstellungen (kPlugin, cName, cWert) VALUES
				(:kPlugin, "activeAttachments", "agb,wrb,dse")',
                [
                    'kPlugin' => $this->getPlugin()->getID(),
                ],
                ReturnType::DEFAULT
            );
        }

        parent::updated($oldVersion, $newVersion);
    }

    /**
     * @param $post_arr
     */
    public function hook132($post_arr): void
    {
        \header('Content-type: application/xml; charset=utf-8');
        $itrkConnector = new ItrkConnector($this->getPlugin(), $this->getDB());
        $itrkConnector->checkPost($post_arr);

        if ($itrkConnector->getNError() === 0) {
            $itrkConnector->saveLegalContent();
        }

        echo $itrkConnector->returnStatus();
        exit();
    }
}
