<?php

declare(strict_types=1);

namespace JTL\Router\Controller;

use JTL\Catalog\Hersteller;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\CMS;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Helpers\URL;
use JTL\Language\LanguageHelper;
use JTL\Link\LinkInterface;
use JTL\Link\SpecialPageNotFoundException;
use JTL\Mapper\LinkTypeToPageType;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Router\ControllerFactory;
use JTL\Session\Frontend;
use JTL\Shipping\DomainObjects\ShippingCartPositionDTO;
use JTL\Shipping\DomainObjects\ShippingDTO;
use JTL\Shop;
use JTL\Sitemap\Sitemap;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Route\RouteGroup;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class PageController
 * @package JTL\Router\Controller
 */
class PageController extends AbstractController
{
    protected string $tseoSelector = 'kLink';

    /**
     * @inheritdoc
     */
    public function init(): bool
    {
        parent::init();
        $this->currentLink = Shop::Container()->getLinkService()->getLinkByID($this->state->linkID);
        if ($this->currentLink === null) {
            return false;
        }
        $this->state->linkType = $this->currentLink->getLinkType();
        $this->state->pageType = (new LinkTypeToPageType())->map($this->currentLink->getLinkType());

        return $this->state->linkType !== \LINKTYP_404;
    }

    /**
     * @inheritdoc
     */
    public function notFoundResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $this->smarty = $smarty;
        if ($this->state->languageID === 0) {
            $this->state->languageID = Shop::getLanguageID();
        }
        $this->state->is404  = true;
        $this->currentLink   = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_404);
        $this->state->linkID = $this->currentLink->getID();
        $this->handle404();
        $this->preRender();
        $this->assignData();

        \executeHook(\HOOK_SEITE_PAGE);

        return $this->smarty->getResponse('layout/index.tpl')->withStatus(404);
    }

    protected function initHome(): void
    {
        try {
            $this->currentLink = Shop::Container()->getLinkService()->getSpecialPage(\LINKTYP_STARTSEITE);
        } catch (SpecialPageNotFoundException) {
            return;
        }
        $this->state->pageType = \PAGE_STARTSEITE;
        $this->state->linkType = \LINKTYP_STARTSEITE;

        $this->updateState(
            (object)[
                'cSeo'     => $this->currentLink->getSEO(),
                'kLink'    => $this->currentLink->getID(),
                'kKey'     => $this->currentLink->getID(),
                'cKey'     => 'kLink',
                'kSprache' => $this->currentLink->getLanguageID()
            ],
            $this->currentLink->getSEO()
        );
    }

    /**
     * @inheritdoc
     */
    public function register(RouteGroup $route, string $dynName): void
    {
        $name = \SLUG_ALLOW_SLASHES ? 'name:.+' : 'name';
        $route->get('/' . \ROUTE_PREFIX_PAGES . '/id/{id:\d+}', $this->getResponse(...))
            ->setName('ROUTE_PAGE_BY_ID' . $dynName);
        $route->get('/' . \ROUTE_PREFIX_PAGES . '/{' . $name . '}', $this->getResponse(...))
            ->setName('ROUTE_PAGE_BY_NAME' . $dynName);
        $route->post('/' . \ROUTE_PREFIX_PAGES . '/id/{id:\d+}', $this->getResponse(...))
            ->setName('ROUTE_PAGE_BY_ID' . $dynName . 'POST');
        $route->post('/' . \ROUTE_PREFIX_PAGES . '/{' . $name . '}', $this->getResponse(...))
            ->setName('ROUTE_PAGE_BY_NAME' . $dynName . 'POST');
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        if (isset($args['id']) || isset($args['name'])) {
            $this->getStateFromSlug($args);
            if (!$this->init()) {
                return $this->notFoundResponse($request, $args, $smarty);
            }
        } elseif ($this->currentLink === null) {
            $this->initHome();
        }
        Shop::setPageType($this->state->pageType);
        $link = $this->validateLink($this->currentLink, $request, $args);
        if ($link instanceof ResponseInterface) {
            return $link;
        }

        match ($link->getLinkType()) {
            \LINKTYP_STARTSEITE       => $this->handleHomePage(),
            \LINKTYP_AGB,
            \LINKTYP_WRB,
            \LINKTYP_WRB_FORMULAR,
            \LINKTYP_DATENSCHUTZ      => $this->handleAGBWRB(),
            \LINKTYP_VERSAND          => $this->handleShipping(),
            \LINKTYP_LIVESUCHE        => $this->handleLiveSearch(),
            \LINKTYP_HERSTELLER       => $this->handleManufacturer(),
            \LINKTYP_NEWSLETTERARCHIV => $this->handleNewsletterArchive(),
            \LINKTYP_SITEMAP          => $this->handleSitemap(),
            \LINKTYP_404              => $this->handle404(),
            \LINKTYP_GRATISGESCHENK   => $this->handleGift(),
            \LINKTYP_AUSWAHLASSISTENT => $this->handleWizzard(),
            default                   => null,
        };
        $this->checkPluginPage();
        $this->preRender();
        $this->assignData();

        \executeHook(\HOOK_SEITE_PAGE);
        $respone = $this->smarty->getResponse('layout/index.tpl');

        return $this->state->is404 ? $respone->withStatus(404) : $respone;
    }

    /**
     * @param array<string, int|string> $args
     */
    private function validateLink(
        ?LinkInterface $link,
        ServerRequestInterface $request,
        array $args
    ): ResponseInterface|LinkInterface {
        if ($link === null) {
            return $this->notFoundResponse($request, $args, $this->smarty);
        }
        if (!$link->isVisible()) {
            $this->initHome();

            return new RedirectResponse($this->currentLink->getURL(), 301);
        }
        if (!\str_contains(URL::buildURL($link, \URLART_SEITE), '.php')) {
            $this->canonicalURL = $link->getURL();
        }
        $mapped = ControllerFactory::getControllerClassByLinkType($link->getLinkType());
        if ($mapped !== null && $mapped !== __CLASS__) {
            return $this->delegateResponse($mapped, $request, $args, $this->smarty);
        }

        return $link;
    }

    protected function getPluginPage(): void
    {
        $linkID = $this->currentLink?->getID() ?? 0;
        if ($linkID <= 0) {
            return;
        }
        $linkFile = $this->db->select('tpluginlinkdatei', 'kLink', $linkID);
        if ($linkFile === null || empty($linkFile->cDatei)) {
            return;
        }
        global $oPlugin, $plugin, $smarty;
        $smarty   = $this->smarty;
        $pluginID = (int)$linkFile->kPlugin;
        $plugin   = PluginHelper::getLoaderByPluginID($pluginID)->init($pluginID);
        $oPlugin  = $plugin;
        $this->smarty->assign('oPlugin', $plugin)
            ->assign('plugin', $plugin)
            ->assign('Link', $this->currentLink);
        if ($linkFile->cTemplate !== null && \mb_strlen($linkFile->cTemplate) > 0) {
            $this->smarty->assign(
                'cPluginTemplate',
                $plugin->getPaths()->getFrontendPath()
                . \PFAD_PLUGIN_TEMPLATE . $linkFile->cTemplate
            )->assign('nFullscreenTemplate', 0);
        } else {
            $this->smarty->assign(
                'cPluginTemplate',
                $plugin->getPaths()->getFrontendPath() .
                \PFAD_PLUGIN_TEMPLATE . $linkFile->cFullscreenTemplate
            )->assign('nFullscreenTemplate', 1);
        }
        include $plugin->getPaths()->getFrontendPath() . $linkFile->cDatei;
    }

    /**
     * @param class-string<ControllerInterface> $class
     * @param array<string, int|string>         $args
     */
    protected function delegateResponse(
        string $class,
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $controller = new $class(
            $this->db,
            $this->cache,
            $this->state,
            $this->config,
            $this->alertService
        );
        $controller->init();

        return $controller->getResponse($request, $args, $smarty);
    }

    private function handleHomePage(): void
    {
        $this->canonicalURL = $this->getHomeURL(Shop::getURL());
        $this->smarty->assign('StartseiteBoxen', CMS::getHomeBoxes())
            ->assign(
                'oNews_arr',
                $this->config['news']['news_benutzen'] === 'Y'
                    ? CMS::getHomeNews($this->config)
                    : []
            );
        Wizard::startIfRequired(\AUSWAHLASSISTENT_ORT_STARTSEITE, 1, $this->languageID, $this->smarty);
    }

    private function handleAGBWRB(): void
    {
        $data = Shop::Container()->getLinkService()->getAGBWRB($this->languageID, $this->customerGroupID);
        $this->smarty->assign('WRB', $data)
            ->assign('AGB', $data);
    }

    private function handleShipping(): void
    {
        if (isset($_POST['versandrechnerBTN']) && (empty($_POST['land']) || empty($_POST['plz']))) {
            $this->alertService->addError(
                Shop::Lang()->get('missingParamShippingDetermination', 'errorMessages'),
                'missingParamShippingDetermination'
            );
        } elseif (isset($_POST['land'], $_POST['plz'])) {
            $error           = Shop::Lang()->get('noDispatchAvailable');
            $shippingMethods = $this->getShippingService()->getPossibleShippingMethods(
                Frontend::getCustomer(),
                Frontend::getCustomerGroup(),
                Request::pString('land'),
                Frontend::getCurrency(),
                Request::pString('plz'),
                Frontend::getCart()->PositionenArr,
            );

            if (empty($shippingMethods) === false) {
                $error = '';
                $this->smarty
                    ->assign(
                        'ArtikelabhaengigeVersandarten',
                        \array_map(
                            static function (ShippingCartPositionDTO $dto): stdClass {
                                return $dto->toLegacyObject();
                            },
                            $shippingMethods[0]->customShippingCosts
                        )
                    )
                    ->assign(
                        'Versandarten',
                        \array_map(
                            static function (ShippingDTO $dto): stdClass {
                                return $dto->toLegacyObject();
                            },
                            $shippingMethods
                        )
                    )
                    ->assign('Versandland', LanguageHelper::getCountryCodeByCountryName(Request::pString('land')))
                    ->assign('VersandPLZ', Text::filterXSS(Request::pString('plz')));
            }
            \executeHook(\HOOK_WARENKORB_PAGE_ERMITTLEVERSANDKOSTEN);
            if ($error !== '') {
                $this->alertService->addError($error, 'shippingCostError');
            }
        }
        $this->smarty->assignDeprecated(
            'laender',
            Shop::Container()->getCountryService()->getCountrylist(),
            '5.5.0',
        );
    }

    private function handleLiveSearch(): void
    {
        $liveSearchTop  = CMS::getLiveSearchTop($this->config);
        $liveSearchLast = CMS::getLiveSearchLast($this->config);
        if (\count($liveSearchTop) === 0 && \count($liveSearchLast) === 0) {
            $this->alertService->addWarning(Shop::Lang()->get('noDataAvailable'), 'noDataAvailable');
        }
        $this->smarty->assign('LivesucheTop', $liveSearchTop)
            ->assign('LivesucheLast', $liveSearchLast);
    }

    private function handleManufacturer(): void
    {
        $this->smarty->assign(
            'oHersteller_arr',
            Hersteller::getAll(true, $this->languageID, $this->customerGroupID)
        );
    }

    private function handleNewsletterArchive(): void
    {
        $this->smarty->assign('oNewsletterHistory_arr', CMS::getNewsletterHistory());
    }

    private function handleSitemap(): void
    {
        Shop::setPageType(\PAGE_SITEMAP);
        $sitemap = new Sitemap($this->db, $this->cache, $this->config);
        $sitemap->assignData($this->smarty);
    }

    private function handle404(): void
    {
        $sitemap = new Sitemap($this->db, $this->cache, $this->config);
        $sitemap->assignData($this->smarty);
        Shop::setPageType(\PAGE_404);
        $this->alertService->addDanger(Shop::Lang()->get('pageNotFound'), 'pageNotFound', ['dismissable' => false]);
    }

    private function handleGift(): void
    {
        if ($this->config['sonstiges']['sonstiges_gratisgeschenk_nutzen'] !== 'Y') {
            return;
        }
        $freeGiftProducts = Shop::Container()->getFreeGiftService()
            ->getFreeGifts($this->config, $this->customerGroupID)
            ->setStillMissingAmounts(
                Frontend::getCart()->gibGesamtsummeWarenExt([\C_WARENKORBPOS_TYP_ARTIKEL], true),
            );
        if (\count($freeGiftProducts) > 0) {
            $this->smarty->assign('freeGifts', $freeGiftProducts)
                ->assignDeprecated(
                    'oArtikelGeschenk_arr',
                    $freeGiftProducts->getProductArray(),
                    '5.4.0'
                );
        } else {
            $this->alertService->addError(
                Shop::Lang()->get('freegiftsNogifts', 'errorMessages'),
                'freegiftsNogifts'
            );
        }
    }

    private function handleWizzard(): void
    {
        Wizard::startIfRequired(
            \AUSWAHLASSISTENT_ORT_LINK,
            $this->currentLink?->getID() ?? 0,
            $this->languageID,
            $this->smarty
        );
    }

    private function checkPluginPage(): void
    {
        if ($this->currentLink === null) {
            return;
        }
        $pluginID = $this->currentLink->getPluginID();
        if ($pluginID <= 0 || $this->currentLink->getPluginEnabled() !== true) {
            return;
        }
        Shop::setPageType(\PAGE_PLUGIN);
        $loader = PluginHelper::getLoaderByPluginID($pluginID, $this->db, $this->cache);
        $boot   = PluginHelper::bootstrap($pluginID, $loader);
        if ($boot === null || !$boot->prepareFrontend($this->currentLink, $this->smarty)) {
            $this->getPluginPage();
        }
    }

    public function assignData(): void
    {
        $this->smarty->assign('Link', $this->currentLink)
            ->assign('bSeiteNichtGefunden', Shop::getPageType() === \PAGE_404)
            ->assign('cFehler')
            ->assign('meta_language', Text::convertISO2ISO639(Shop::getLanguageCode()));
    }
}
