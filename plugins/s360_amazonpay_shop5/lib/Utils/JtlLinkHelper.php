<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\Router\Route;
use JTL\Shop;

/**
 * Class JtlLinkHelper
 *
 * Helps resolving plugin links.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class JtlLinkHelper {
    use JtlLoggerTrait;

    /**
     * @var \JTL\Plugin\PluginInterface|null $plugin
     */
    private $plugin;

    /**
     * @var JtlLinkHelper $instance 
     */
    private static $instance;

    private function __construct() {
        $this->plugin = Plugin::getInstance();
    }

    public static function getInstance(): JtlLinkHelper {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public const FRONTEND_FILE_CALLBACK_RESULT = 'result.php';
    public const FRONTEND_FILE_IPN = 'ipn.php';
    public const FRONTEND_FILE_CHECKOUT = 'checkout.php';
    public const FRONTEND_FILE_LOGIN = 'login.php';
    public const FRONTEND_FILE_RETURN = 'return.php';
    public const FRONTEND_FILE_APB_REDIRECT = 'apb_redirect.php';
    public const FRONTEND_FILE_AUTO_KEY_EXCHANGE = 'auto_key_exchange.php';
    public const FRONTEND_FILE_SUBSCRIPTION_CUSTOMER = 'subscription_customer.php';

    public const PLUGIN_FRONTEND_LINK_TYPE = 'amazonPayFrontendLinkType';

    /**
     * Note that these must match with the entries in the info.xml
     */
    public const ADMIN_TAB_ORDERS = 'Übersicht';
    public const ADMIN_TAB_ACCOUNT = 'Einrichtung';
    public const ADMIN_TAB_CONFIG = 'Einstellungen';
    public const ADMIN_TAB_SUBSCRIPTION_OVERVIEW = 'Abo: Übersicht';
    public const ADMIN_TAB_SUBSCRIPTION_CONFIG = 'Abo: Einstellungen';

    /**
     * Given a filename of a frontendlink (of this plugin), it returns the appropriate link to it (w.r.p.t. the current language!)
     * @param string $fileName the name of the frontendlink file in this plugin (e.g. "ipn.php")
     * @param int $languageId
     * @return null|string
     */
    public function getFullUrlForFrontendFile(string $fileName, int $languageId = 0) :?string {
        try {
            $kSprache = $languageId;
            if($kSprache <= 0) {
                $kSprache = Shop::getLanguageID();
            }
            if (null !== $this->plugin && !empty($kSprache)) {
                $queryPrepared = 'SELECT * FROM tpluginlinkdatei tpl, tseo ts WHERE ts.cKey = "kLink" AND ts.kKey = tpl.kLink AND tpl.kPlugin = :kPlugin AND tpl.cDatei = :cDatei AND ts.kSprache = :kSprache';
                $result = Shop::Container()->getDB()->executeQueryPrepared($queryPrepared, ['kPlugin' => $this->plugin->getID(), 'cDatei' => $fileName, 'kSprache' => $kSprache], ReturnType::SINGLE_OBJECT);
                if (!empty($result)) {
                    return Shop::getURL(true) . '/' . $result->cSeo;
                }
                // if result is empty, fall back to default language if we have not tested it yet
                $kSpracheDefault = Shop::getLanguageID();
                if($kSpracheDefault !== $kSprache) {
                    $result = Shop::Container()->getDB()->executeQueryPrepared($queryPrepared, ['kPlugin' => $this->plugin->getID(), 'cDatei' => $fileName, 'kSprache' => $kSpracheDefault], ReturnType::SINGLE_OBJECT);
                    if (!empty($result)) {
                        return Shop::getURL(true) . '/' . $result->cSeo;
                    }
                }
            }
            return null;
        } catch (\InvalidArgumentException | \Exception $exception) {
            $this->debugLog('Exception while trying to get frontend link url for file "' . $fileName . '": ' . $exception->getMessage(), 'JtlLinkHelper');
            return null;
        }
    }

    /**
     * Given a filename of an adminmenu custom link, returns the full url, including the tab-indicator fragment.
     * @param string $fileName
     * @return array|null|string
     */
    public function getFullUrlForAdminTab(string $tabName): ?string {
        try {
            $queryPrepared = 'SELECT * FROM tpluginadminmenu WHERE kPlugin = :kPlugin AND cName = :cName';
            $result = Shop::Container()->getDB()->executeQueryPrepared($queryPrepared, ['kPlugin' => $this->plugin->getID(), 'cName' => $tabName], ReturnType::SINGLE_OBJECT);
            if (!empty($result)) {
                if(version_compare(\APPLICATION_VERSION, '5.2.0', '>=')) {
                    return Shop::getURL(true) . '/' . \PFAD_ADMIN . Route::PLUGIN . '/' . $this->plugin->getID() . '#plugin-tab-' . $result->kPluginAdminMenu;
                } else {
                    return Shop::getURL(true) . '/' . \PFAD_ADMIN .'plugin.php?kPlugin=' . $this->plugin->getID() . '#plugin-tab-' . $result->kPluginAdminMenu;
                }
            }
            return null;
        } catch (\InvalidArgumentException | \Exception $exception) {
            $this->debugLog('Exception while trying to get admin link url for file "' . $tabName . '": ' . $exception->getMessage(), 'JtlLinkHelper');
            return null;
        }
    }

    /**
     * Returns the return URL, note that this URL is always for the default language!
     */
    public function getFullReturnUrl() {
        $defaultLanguage = LanguageHelper::getDefaultLanguage(true);
        return $this->getFullUrlForFrontendFile(self::FRONTEND_FILE_RETURN, (int) $defaultLanguage->kSprache);
    }


    /**
     * Returns the URL to the plugin backend (note that this is not the same as the url pathwise to the plugins adminmenu folder)
     * @return string
     */
    public function getFullAdminUrl() {
        if(version_compare(\APPLICATION_VERSION, "5.2.0", ">=")) {
            return Shop::getURL(true) . '/' . \PFAD_ADMIN . Route::PLUGIN . '/' . $this->plugin->getID();
        } else {
            return Shop::getURL(true) . '/' . \PFAD_ADMIN .'plugin.php?kPlugin=' . $this->plugin->getID();
        }
    }

    /**
     * Returns all frontend urls.
     * @return array
     */
    public function getAllFrontendUrls(): array {
        $result = [
            'callback_result' => $this->getFullUrlForFrontendFile(self::FRONTEND_FILE_CALLBACK_RESULT),
            'checkout' => $this->getFullUrlForFrontendFile(self::FRONTEND_FILE_CHECKOUT),
            'ipn' => $this->getFullUrlForFrontendFile(self::FRONTEND_FILE_IPN),
            'login' => $this->getFullUrlForFrontendFile(self::FRONTEND_FILE_LOGIN)
        ];
        return $result;
    }

    /**
     * Returns the shop domain.
     * In most cases this will be the same as the shop URL, but not if the shop is in a subdirectory.
     *
     * @return string the shop domain
     */
    public function getShopDomain(): string {
        $url = Shop::getURL(true);
        return parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
    }

    /**
     * Tries to determine the privacy notice url.
     * Might return empty string if it fails to do that.
     * @param int $languageId
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getPrivacyNoticeUrl(int $languageId = 0): string {
        $kSprache = $languageId;
        if($kSprache <= 0) {
            $kSprache = LanguageHelper::getDefaultLanguage(true)->kSprache;
        }
        // determine iso
        $cISOSprache = LanguageHelper::getLanguageDataByType('', $kSprache);

        $stmt = 'SELECT tls.cISOSprache as lang, tls.cSeo as seo FROM tlink tl, tlinksprache tls WHERE tls.kLink = tl.kLink AND tls.cISOSprache = :cISOSprache AND tl.nLinkart = ' . \LINKTYP_DATENSCHUTZ;
        $res = Shop::Container()->getDB()->executeQueryPrepared($stmt, ['cISOSprache' => $cISOSprache], ReturnType::SINGLE_OBJECT);
        if(!empty($res) && !empty($res->seo)) {
            return Shop::getURL(true) . '/' . $res->seo;
        }
        return '';
    }

    /**
     * Determines if the current page is a plugin frontend link of this plugin.
     * This can be done by inspecting the Shop-Repository values.
     */
    public function isPluginFrontendLink(): bool {
        if(Shop::has(self::PLUGIN_FRONTEND_LINK_TYPE)) {
            return true;
        }
        return false;
    }
}