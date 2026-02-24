<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use JTL\Cart\CartItem;
use JTL\Checkout\Bestellung;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Models\StoreModel;
use Plugin\s360_clerk_shop5\src\Utils\AttributesHelper;
use Plugin\s360_clerk_shop5\src\Utils\Config;

final class FrontendController extends Controller {

    use AttributesHelper;

    public const TEMPLATE_BASIC_CONFIG = 'template/clerk_config';
    public const TEMPLATE_SALES_TRACKING = 'template/clerk_sales_tracking';
    public const TEMPLATE_OMNI_SEARCH = 'template/clerk_omni_search';
    public const TEMPLATE_LIVE_SEARCH = 'template/clerk_live_search';
    public const TEMPLATE_PRODUCT_SLIDER = 'template/clerk_article';
    public const TEMPLATE_CATEGORY_SLIDER = 'template/clerk_category';
    public const TEMPLATE_SHOPPING_CART = 'template/clerk_shopping_cart';
    public const TEMPLATE_POWER_STEP = 'template/clerk_power_step';
    public const TEMPLATE_EXIT_INTENT = 'template/clerk_exit_intent';

    public function handle(): void {
        $model = new StoreModel();
        $store = $model->getCurrentStore();

        if (empty($store)) {
            return;
        }

        $clerkJs = empty($this->plugin->getConfig()->getValue(Config::SETTING_CUSTOM_CLERK_JS_NAME)) ? 'cdn.clerk.io/clerk.js' : sprintf('custom.clerk.io/%s.js', $this->plugin->getConfig()->getValue(Config::SETTING_CUSTOM_CLERK_JS_NAME));

        $this->smarty->assign('s360_clerk_js', $clerkJs);
        $this->smarty->assign('s360_clerk_store', $store);
        $this->smarty->assign('s360_clerk_settings', $this->plugin->getConfig());
        $this->smarty->assign(
            's360_clerk_cart',
            array_map(fn(CartItem $item) => $item->kArtikel, Frontend::getCart()->PositionenArr)
        );

        $this->render(self::TEMPLATE_BASIC_CONFIG, 'append', 'body');

        $this->handleExitIntent();
        $this->handleOmniSearch();
        $this->liveSearch();

        switch (Shop::getPageType()) {
            case \PAGE_ARTIKEL:
                $this->articlePage();
                break;
            case \PAGE_ARTIKELLISTE:
                $this->categoryPage();
                break;
            case \PAGE_BESTELLABSCHLUSS:
                $this->salesTracking();
                break;
            case \PAGE_WARENKORB:
                $this->shoppingCart();
                break;
        }
    }

    public function handleSearchResults(LinkInterface $link): void {
        if ($this->plugin->getConfig()->getValue(Config::SETTING_SEARCHPAGE_ACTIVE) !== 'on') {
            return;
        }

        // TODO: constructor
        $model = new StoreModel();
        $store = $model->getCurrentStore();

        if (empty($store)) {
            return;
        }

        // set title
        $title = $this->plugin->getLocalization()->getTranslation('search_headline')
            . ' ' . Text::filterXSS(Request::getVar('query', ''));
        $link->setTitle($title);
        $link->setMetaTitle($title);

        // check for custom template
        $templateNameCustom = 'clerk_searchresults_custom';
        $templateCustom = $this->plugin->getPaths()->getFrontendPath() . "template/{$templateNameCustom}.tpl";

        if (file_exists($templateCustom)) {
            $link->setTemplate($templateNameCustom . ".tpl");
        }

        $this->smarty->assign('s360_clerk_store', $store);
        $this->smarty->assign('s360_clerk_settings', $this->plugin->getConfig());

        // Facets
        if ($this->plugin->getConfig()->getValue(Config::SETTING_FACETS_POSITION) !== 'none') {
            $this->smarty->assign('s360_clerk_facets', [
                'attributes' => $this->getFacetAttributes($this->plugin),
                'multiselect_attributes' => $this->getFacetMultiAttributes($this->plugin),
                'titles' => $this->getFacetAttributeTitles(),
                'design' => $store->getSettings()?->getFacetsDesign(),
                'in_url' => $this->plugin->getConfig()->getValue(Config::SETTING_FACETS_IN_URL),
                'position' => $this->plugin->getConfig()->getValue(Config::SETTING_FACETS_POSITION),
            ]);
        }
    }

    public function shoppingCart(): void {

        if ($this->plugin->getConfig()->getValue(Config::SETTING_SHOPPINGCART_ACTIVE) !== 'on') {
            return;
        }

        $cart = Frontend::getCart();

        if (empty($cart)) {
            return;
        }

        $shoppingcartdata = [];

        foreach ($cart->PositionenArr as $output) {
            if ($output->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                $tmpId = $output->kArtikel;

                if ($output->Artikel->kVaterArtikel > 0) {
                    $tmpId = $output->Artikel->kVaterArtikel;
                }

                $shoppingcartdata[] = $tmpId;
            }
        }


        $this->smarty->assign('s360_clerk_shoppingcart', [
            'product' => $shoppingcartdata,
            'exclude' => $this->plugin->getConfig()->getValue(Config::SETTING_SHOPPINGCART_EXCLUDE_DUPLICATES) === 'on',
            'template' => $this->plugin->getConfig()->getValue(Config::SETTING_SHOPPINGCART_TEMPLATE)
        ]);

        $this->render(
            self::TEMPLATE_SHOPPING_CART,
            $this->plugin->getConfig()->getValue(Config::SETTING_SHOPPINGCART_SLIDER_POSITION),
            $this->plugin->getConfig()->getValue(Config::SETTING_SHOPPINGCART_SLIDER_SELECTOR)
        );
    }

    public function categoryPage(): void {
        if ($this->plugin->getConfig()->getValue(Config::SETTING_CATEGORY_SLIDER_ACTIVE) !== 'on') {
            return;
        }

        $this->smarty->assign('s360_clerk_category', [
            'exclude' => $this->plugin->getConfig()->getValue(Config::SETTING_CATEGORY_EXCLUDE_DUPLICATES) === 'on',
            'template' => $this->plugin->getConfig()->getValue(Config::SETTING_CATEGORY_TEMPLATE)
        ]);

        $this->render(
            self::TEMPLATE_CATEGORY_SLIDER,
            $this->plugin->getConfig()->getValue(Config::SETTING_CATEGORY_SLIDER_POSITION),
            $this->plugin->getConfig()->getValue(Config::SETTING_CATEGORY_SLIDER_SELECTOR)
        );
    }

    public function articlePage(): void {
        // Recomm Slider
        if ($this->plugin->getConfig()->getValue(Config::SETTING_ARTICLE_SLIDER_ACTIVE) === 'on') {
            $this->smarty->assign('s360_clerk_article', [
                'exclude' => $this->plugin->getConfig()->getValue(Config::SETTING_ARTICLE_EXCLUDE_DUPLICATES) === 'on',
                'template' => $this->plugin->getConfig()->getValue(Config::SETTING_ARTICLE_TEMPLATE)
            ]);

            $this->render(
                self::TEMPLATE_PRODUCT_SLIDER,
                $this->plugin->getConfig()->getValue(Config::SETTING_ARTICLE_SLIDER_POSITION),
                $this->plugin->getConfig()->getValue(Config::SETTING_ARTICLE_SLIDER_SELECTOR)
            );
        }

        // Power-Step
        if ($this->plugin->getConfig()->getValue(Config::SETTING_POWERSTEP_ACTIVE) === 'on') {
            $this->smarty->assign('s360_clerk_article', [
                'exclude' => $this->plugin->getConfig()->getValue(Config::SETTING_POWERSTEP_EXCLUDE_DUPLICATES) === 'on',
                'template' => $this->plugin->getConfig()->getValue(Config::SETTING_POWERSTEP_TEMPLATE)
            ]);

            $this->render(
                self::TEMPLATE_POWER_STEP,
                $this->plugin->getConfig()->getValue(Config::SETTING_POWERSTEP_POSITION),
                $this->plugin->getConfig()->getValue(Config::SETTING_POWERSTEP_SELECTOR)
            );
        }
    }

    public function handleExitIntent(): void {
        if ($this->plugin->getConfig()->getValue(Config::SETTING_EXIT_INTENT_ACTIVE) !== 'on') {
            return;
        }

        $this->smarty->assign('s360_clerk_exit_intent', [
            'template' => $this->plugin->getConfig()->getValue(Config::SETTING_EXIT_INTENT_TEMPLATE)
        ]);

        $this->render(self::TEMPLATE_EXIT_INTENT, 'append', 'body');
    }

    public function salesTracking(): void {
        /** @var Bestellung|null $order */
        $order = $this->smarty->getTemplateVars('Bestellung');

        if (empty($order)) {
            return;
        }

        $trackVariant = (bool) $this->plugin->getConfig()->getValue("track_variant_products");

        $salestrackingdata = [];
        foreach ($order->Positionen as $output) {
            if ($output->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                $tmpId = $output->kArtikel;

                if (!$trackVariant && $output->Artikel->kVaterArtikel > 0) {
                    $tmpId = $output->Artikel->kVaterArtikel;
                }

                $salestrackingdata[] = [
                    'id' => $tmpId,
                    'quantity' => $output->nAnzahl,
                    'price' => $output->fPreisEinzelNetto * ($output->fMwSt / 100 + 1)
                ];
            }
        }

        $mail = $order->oKunde->cMail;
        if ($this->plugin->getConfig()->getValue(Config::SETTING_HASHED_MAILS) == 'on') {
            list($localPart, $domainPart) = explode('@', $mail, 2);

            if ($localPart && $domainPart) {
                $mail = md5($localPart) . '@' . $domainPart;
            }
        }

        $this->smarty->assign('s360_clerk_sales_tracking', [
            'positions' => $salestrackingdata,
            'email' => $mail
        ]);

        $this->render(self::TEMPLATE_SALES_TRACKING, 'append', 'body');
    }

    private function getFacetAttributeTitles(): array {
        $lang = Shop::getLanguageID();
        $attrs = array_unique(
            array_merge($this->getFacetAttributes($this->plugin), $this->getFacetMultiAttributes($this->plugin))
        );

        $result = Shop::Container()->getDB()->queryPrepared(
            "SELECT * FROM tmerkmalsprache WHERE kSprache = :lang",
            ['lang' => $lang],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );

        $titles = [];
        foreach ($result as $row) {
            $key = $this->transformAttributeName((string)$row['cName']);
            if (in_array($key, $attrs)) {
                $titles[$key] = $row['cName'];
            }
        }

        foreach ($this->getConfigAttributesStandard() as $key => $row) {
            if (in_array($key, $attrs)) {
                $titles[$key] = $row;
            }
        }

        return $titles;
    }

    /**
     * get attributes standard
     *
     * @return array
     */
    private function getConfigAttributesStandard(): array {
        return [
            'categories' => $this->plugin->getLocalization()->getTranslation(Config::TRANS_FACETS_HEADLINE_CATEGORY),
            'brand' => $this->plugin->getLocalization()->getTranslation(Config::TRANS_FACETS_HEADLINE_BRAND),
            'price' => $this->plugin->getLocalization()->getTranslation(Config::TRANS_FACETS_HEADLINE_PRICE)
        ];
    }

    public function handleOmniSearch(): void {
        if (
            $this->plugin->getConfig()->getValue(Config::SETTING_OMNI_SEARCH_ACTIVE) !== 'on' ||
            $this->plugin->getConfig()->getValue(Config::SETTING_OMNI_SEARCH_INSERT_METHOD) === 'injection'
        ) {
            return;
        }

        $this->smarty->assign('s360_clerk_omni_search', [
            'selector' => $this->plugin->getConfig()->getValue(Config::SETTING_OMNI_SEARCH_SELECTOR),
            'template' => $this->plugin->getConfig()->getValue(Config::SETTING_OMNI_SEARCH_TEMPLATE),
            's360_ClerkPlugin' => $this->plugin
        ]);

        $this->render(self::TEMPLATE_OMNI_SEARCH, 'append', 'body');
    }

    public function liveSearch(): void {
        if (
            $this->plugin->getConfig()->getValue(Config::SETTING_LIVESEARCH_ACTIVE) !== 'on' ||
            $this->plugin->getConfig()->getValue(Config::SETTING_OMNI_SEARCH_ACTIVE) === 'on'
        ) {
            return;
        }

        $this->smarty->assign('s360_clerk_livesearch', [
            'selector' => $this->plugin->getConfig()->getValue(Config::SETTING_LIVESEARCH_SELECTOR),
            'template' => $this->plugin->getConfig()->getValue(Config::SETTING_LIVESEARCH_TEMPLATE),
            'search_suggestions' => $this->plugin->getConfig()->getValue(Config::SETTING_COUNT_SEARCH_SUGGESTIONS),
            'category_suggestions' => $this->plugin->getConfig()->getValue(Config::SETTING_COUNT_CATEGORY_SUGGESTIONS),
            'page_suggestions' => $this->plugin->getConfig()->getValue(Config::SETTING_COUNT_PAGE_SUGGESTIONS),
            'position_livesearch' => $this->plugin->getConfig()->getValue(Config::SETTING_LIVESEARCH_POSITION),
            's360_ClerkPlugin' => $this->plugin
        ]);

        /** @var Link|null $link */
        $link = $this->plugin->getLinks()->getLinks()->first(
            static fn(Link $link) => $link->getIdentifier() === Config::PAGE_SEARCH_RESULTS
        );

        if ($link) {
            $this->smarty->assign("s360_search_cSeo", $link->getURL());
        }

        $this->render(self::TEMPLATE_LIVE_SEARCH, 'append', 'body');
    }
}
