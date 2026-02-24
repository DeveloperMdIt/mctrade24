<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Alert\Alert;
use JTL\Catalog\Product\Artikel;
use JTL\Filter\SearchResults;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidConfigurationException;
use Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException;
use Plugin\s360_amazonpay_shop5\lib\Frontend\Button;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class FrontendOutputController
 *
 * Handles frontend output where necessary.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class FrontendOutputController {

    use JtlLoggerTrait;


    /**
     * @var PluginInterface $plugin
     */
    private $plugin;

    /**
     * @var Config $config
     */
    private $config;

    private $addedLoginButtonIds;
    private $addedPayButtonIds;
    private $isBehavioralOverlayDisplay;
    private $isResourcesRequired;

    /**
     * @var AmazonPay $paymentMethodModule
     */
    private $paymentMethodModule;

    private const TEMPLATE_ID_FRONTEND_SNIPPET_BODY = 'snippet_body';
    private const TEMPLATE_ID_FRONTEND_SNIPPET_HEAD_RETURN_JS = 'head_return_js';
    private const TEMPLATE_ID_FRONTEND_SNIPPET_BEHAVIORAL_OVERLAY = 'behavioral_overlay';
    private const TEMPLATE_ID_FRONTEND_SNIPPET_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE = 'subscription_customer_subscriptions_available';

    private const TEMPLATE_PATHS = [
        self::TEMPLATE_ID_FRONTEND_SNIPPET_BODY => '/frontend/template/snippets/body',
        self::TEMPLATE_ID_FRONTEND_SNIPPET_HEAD_RETURN_JS => '/frontend/template/snippets/head_return_js',
        self::TEMPLATE_ID_FRONTEND_SNIPPET_BEHAVIORAL_OVERLAY => '/frontend/template/snippets/behavioral_overlay',
        self::TEMPLATE_ID_FRONTEND_SNIPPET_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE => '/frontend/template/snippets/subscription_customer_subscriptions_available'
    ];

    private const BEHAVIORAL_OVERLAY_SELECTOR = '#lpa-behavioral-overlay-button-placeholder';
    private const ALERT_SELECTOR = '[data-key="lpaCheckoutErrorNotLoggedIn"]';

    public function __construct(PluginInterface $plugin) {
        $this->plugin = $plugin;
        $this->config = Config::getInstance();
        $this->addedLoginButtonIds = [];
        $this->addedPayButtonIds = [];
        $this->isResourcesRequired = false;
        $this->isBehavioralOverlayDisplay = false;
        $this->paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin), 0);
    }

    /**
     * @throws \Exception
     */
    public function handleSmartyOutput(): void {

        // The return page signaled to us to render the required JS into the head.
        if (Shop::get('lpaIsReturnRequired') ?? false) {
            // render the JS and add it to the head
            pq('head')->prepend(Shop::Smarty()->fetch($this->getTemplateFilePath(self::TEMPLATE_ID_FRONTEND_SNIPPET_HEAD_RETURN_JS)));
            // skip any other computations, the JS will lead to a redirect anyway
            return;
        }

        try {
            $this->prepareSmartyVariables();
            $this->addBehavioralOverlay();
            $this->addButtons();
            $this->addCustomerSubscriptionsLink();
            $this->addRequiredResources();
        } catch (InvalidConfigurationException $e) {
            // cancel any further actions: log error and return without adding anything to the output.
            $this->errorLog($e->getMessage(), __CLASS__);
            return;
        }
    }

    /**
     * Assigns Smarty variables.
     *
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidConfigurationException
     */
    private function prepareSmartyVariables(): void {
        $lpa = [];
        $lpa['sandbox'] = (int)$this->config->isSandbox();
        $lpa['checkoutEndpointUrl'] = $this->config->getCheckoutEndpointUrl();
        $lpa['loginEndpointUrl'] = $this->config->getLoginEndpointUrl();
        $lpa['shopVersion'] = \APPLICATION_VERSION;
        $lpa['pluginVersion'] = (string) $this->plugin->getCurrentVersion();
        $lpa['frontendUrls'] = JtlLinkHelper::getInstance()->getAllFrontendUrls();
        $lpa['pluginFrontendUrl'] = $this->plugin->getPaths()->getFrontendURL(); // ends with /
        $lpa['forceLogout'] = false;
        // check if we have to logout the user from Amazon (we try to detect the logout by the loggedout-parameter as there is no hook for logouts
        if (SessionController::get(SessionController::KEY_FORCE_LOGOUT)
            || (Shop::getPageType() === PAGE_MEINKONTO && isset($_REQUEST['loggedout']) && (int)$_REQUEST['loggedout'] === 1)
        ) {
            $lpa['forceLogout'] = true;
            // make sure, logout is forced only once
            SessionController::clear(SessionController::KEY_FORCE_LOGOUT);
            $this->isResourcesRequired = true;
        }
        Shop::Smarty()->assign('lpa', $lpa);
    }

    /**
     * Gets the path to the snippet referenced by its id
     * @param string $templateId
     * @return string
     */
    private function getTemplateFilePath(string $templateId): string {
        $result = $this->plugin->getPaths()->getBasePath() . self::TEMPLATE_PATHS[$templateId];
        if (file_exists($result . '_custom.tpl')) {
            return $result . '_custom.tpl';
        }
        return $result . '.tpl';
    }

    /**
     * Adds buttons.
     */
    private function addButtons(): void {
        try {
            if (!isset($_GET['lpa-show-buttons']) && $this->config->isHiddenButtonMode()) {
                // hidden button mode, but lpa-show-buttons is not set, render no buttons.
                return;
            }
            if ($this->config->isButtonLoginActive()) {
                $pqSelector = $this->config->getButtonLoginPqSelector();
                if(!empty($pqSelector)) {
                    $selectedTargets = pq($pqSelector);
                    $pqMethod = $this->config->getButtonLoginPqMethod();
                    if ($selectedTargets->length > 0) {
                        $this->isResourcesRequired = true;
                        foreach ($selectedTargets as $target) {
                            $button = new Button(Button::TYPE_LOGIN, Button::CONTEXT_LOGIN);
                            pq($target)->$pqMethod($button->render());
                            $this->addedLoginButtonIds[] = $button->getId();
                        }
                    }
                }
            }
            $customerGroupAllowedForPayMethod = $this->paymentMethodModule->isCustomerGroupValid();
            if ($customerGroupAllowedForPayMethod && $this->config->isButtonPayActive()) {
                $pqSelector = $this->config->getButtonPayPqSelector();
                if(!empty($pqSelector)) {
                    $selectedTargets = pq($pqSelector);
                    $pqMethod = $this->config->getButtonPayPqMethod();
                    if ($selectedTargets->length > 0) {
                        // The check for excluded products in the basket is relatively expensive - we do not want to do it unless we know that there is a pay button to be potentially rendered
                        if ($this->isBehavioralOverlayDisplay || $this->paymentMethodModule->isValid(Frontend::getCustomer(), Frontend::getCart())) {
                            $this->isResourcesRequired = true;
                            /*
                             * Compute availability of subscriptions - subscriptions on the cart page are only possible under certain conditions.
                             */
                            $subscriptionIntervals = [];
                            $subscriptionDiscountRate = '';
                            if($this->paymentMethodModule->isSubscriptionPossibleForCart() && $this->config->isSubscriptionDisplayCart()) {
                                // Get available intervals
                                $subscriptionIntervals = $this->paymentMethodModule->getPossibleSubscriptionIntervalsForCart();
                                $subscriptionDiscountRate = $this->paymentMethodModule->getSubscriptionDiscountRateForCart();

                                // Round the discount rate to an easy to read display
                                $subscriptionDiscountRate = (string) (floor($subscriptionDiscountRate * 100) / 100);

                                if(!empty(Frontend::getCurrency()->getDecimalSeparator())) {
                                    $subscriptionDiscountRate = str_replace('.', Frontend::getCurrency()->getDecimalSeparator(), $subscriptionDiscountRate);
                                }
                            }
                            foreach ($selectedTargets as $target) {
                                $button = new Button(Button::TYPE_PAY, Button::CONTEXT_PAY_GLOBAL, $subscriptionIntervals,$subscriptionDiscountRate);
                                pq($target)->$pqMethod($button->render());
                                $this->addedPayButtonIds[] = $button->getId();
                            }
                        }
                    }
                }
                // and add the button to the behavioral display overlay
                if ($this->isBehavioralOverlayDisplay) {
                    $this->isResourcesRequired = true;
                    $button = new Button(Button::TYPE_PAY, Button::CONTEXT_PAY_GLOBAL);
                    pq(self::BEHAVIORAL_OVERLAY_SELECTOR)->append($button->render());
                    $this->addedPayButtonIds[] = $button->getId();
                }
                // maybe we rendered an alert where we have to display the button in, too
                $alertTarget = pq(self::ALERT_SELECTOR);
                if($alertTarget->length > 0) {
                    $this->isResourcesRequired = true;
                    $button = new Button(Button::TYPE_PAY, Button::CONTEXT_PAY_GLOBAL);
                    $alertTarget->append($button->render());
                    $this->addedPayButtonIds[] = $button->getId();
                }
            }
            if ($customerGroupAllowedForPayMethod && Shop::getPageType() === PAGE_ARTIKEL && $this->config->isButtonPayDetailActive() && !$this->isAjaxRequestForListingExpress()) {
                $this->isResourcesRequired = true; // we have to make sure that we *could* display a pay button, e.g. after a variation is loaded
                // add express button, if possible
                /** @var Artikel $product */
                $product = Shop::Smarty()->getTemplateVars('Artikel');
                $pqSelector = $this->config->getButtonPayDetailPqSelector();
                if(!empty($pqSelector)) {
                    $selectedTargets = pq($pqSelector);
                    $pqMethod = $this->config->getButtonPayDetailPqMethod();
                    if ($selectedTargets->length > 0) {
                        if ($this->isExpressBuyable($product) && !$this->paymentMethodModule->containsExcludedProducts(Frontend::getCart())) {
                            /*
                             * Compute availability of subscriptions - subscriptions on the detail page are ONLY available if the cart is empty!
                             *
                             */
                            $subscriptionDiscountRate = 0;
                            $subscriptionIntervals = [];
                            if($this->paymentMethodModule->isExpressSubscriptionPossible($product) && $this->config->isSubscriptionDisplayDetail()) {
                                // Get available intervals
                                $subscriptionIntervals = $this->paymentMethodModule->getPossibleSubscriptionIntervalsForProduct($product);
                                $subscriptionDiscountRate = $this->paymentMethodModule->getSubscriptionDiscountRateForProduct($product);
                            }

                            foreach ($selectedTargets as $target) {
                                $button = new Button(Button::TYPE_PAY, Button::CONTEXT_PAY_DETAIL, $subscriptionIntervals, (string) $subscriptionDiscountRate);
                                pq($target)->$pqMethod($button->render());
                                $this->addedPayButtonIds[] = $button->getId();
                            }
                        }
                    }
                }
            }
            if ($customerGroupAllowedForPayMethod && $this->config->isButtonPayCategoryActive() && (Shop::getPageType() === PAGE_ARTIKELLISTE || $this->isAjaxRequestForListingExpress()) && !$this->paymentMethodModule->containsExcludedProducts(Frontend::getCart()) ) {
                $this->isResourcesRequired = true; // we have to make sure that we *could* display a pay button, e.g. after a variation is loaded
                // add express button(s) for category page, if possible
                $productIds = $this->getEligibleListingProductIds();
                $pqSelector = $this->config->getButtonPayCategoryPqSelector();
                if(!empty($pqSelector)) {
                    $pqMethod = $this->config->getButtonPayCategoryPqMethod();
                    foreach ($productIds as $productId) {
                        // insert productId in the pq selector
                        $fullSelector = str_ireplace('#kArtikel#', (string)$productId, $pqSelector);
                        if ($fullSelector === $pqSelector) {
                            // no replacement was made, this is per definition wrong (it would mean that the placeholder is missing) and the insertion process will be skipped
                            continue;
                        }
                        // sanity check - the actual display of basket buttons is dependend on the template and we cannot control this from here.
                        // therefore, the wk button is ONLY added IFF the buy_form has a quantity field, indicating that the article could actually be put into the basket.
                        if (!pq('#quantity' . $productId)->length) {
                            // no quantity input field for this article, skip
                            continue;
                        }
                        $selectedTargets = pq($fullSelector);
                        foreach ($selectedTargets as $target) {
                            $button = new Button(Button::TYPE_PAY, Button::CONTEXT_PAY_CATEGORY);
                            pq($target)->$pqMethod($button->render(['productId' => $productId]));
                            $this->addedPayButtonIds[] = $button->getId();
                        }
                    }
                }
            }
        } catch (\Exception|TechnicalException|\SmartyException $e) {
            // log exception
            $this->debugLog('Adding buttons failed with exception: ' . $e->getMessage(), __CLASS__);
        }
    }

    /**
     * Adds required resources, like the body js or css links.
     * @throws \Exception
     */
    private function addRequiredResources(): void {
        if ($this->isResourcesRequired || JtlLinkHelper::getInstance()->isPluginFrontendLink()) {
            $version = $this->plugin->getCurrentVersion()->getOriginalVersion();
            // Add CSS, but make it load "asynchronously". @see https://www.filamentgroup.com/lab/load-css-simpler/
            pq('head')->append("<link type=\"text/css\" href=\"" . $this->plugin->getPaths()->getFrontendURL() . "template/css/lpa.min.css?v=".$version."\" rel=\"stylesheet\" media=\"print\" onload=\"this.media='all'; this.onload = null;\">");
            if (file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/css/lpa_custom.css')) {
                pq('head')->append("<link type=\"text/css\" href=\"" . $this->plugin->getPaths()->getFrontendURL() . "template/css/lpa_custom.css?v=".$version."\" rel=\"stylesheet\" media=\"print\" onload=\"this.media='all'; this.onload = null;\">");
            }
            pq('body')->append(Shop::Smarty()->fetch($this->getTemplateFilePath(self::TEMPLATE_ID_FRONTEND_SNIPPET_BODY)));
        }
    }

    /**
     * Checks if a product is express buyable in theory.
     * @param Artikel $product
     * @return bool
     */
    private function isExpressBuyable(Artikel $product): bool {
        if ($this->paymentMethodModule->isExcludedProduct($product)) {
            return false;
        }
        if (!empty($product->nIstVater) && (int)$product->kVaterArtikel === 0) {
            // current article is a father article, this cant be bought
            return false;
        }
        if (!((int)$product->inWarenkorbLegbar === 1)) {
            // product cannot be put into the basket
            return false;
        }
        if (!empty($product->oKonfig_arr)) {
            // product is a configurator article
            return false;
        }
        if (null === $product->Preise->fVKNetto || $product->Preise->fVKNetto <= 0) {
            // product has no price
            return false;
        }
        if(!empty($product->Preise->fVKBrutto)) {
            $totalSum = Frontend::getCart()->gibGesamtsummeWaren(true) + $product->Preise->fVKBrutto * max($product->fMindestbestellmenge, 1);
        } else {
            // fall back to fVKNetto
            $totalSum = Frontend::getCart()->gibGesamtsummeWaren(true) + $product->Preise->fVKNetto * max($product->fMindestbestellmenge, 1);
        }
        if ($this->paymentMethodModule->getSetting('min') > $totalSum) {
            // total sum is not enough to immediately go to checkout with amazon (payment method has higher min order value)
            return false;
        }
        if (Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT) !== null && Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT) > $totalSum) {
            // total sum is not enough to immediately go to checkout (customer group has higher min order value)
            return false;
        }

        return true;
    }

    private function isAjaxRequestForListingExpress(): bool {
        return (Shop::getPageType() === PAGE_ARTIKEL && Request::isAjaxRequest() && isset($_GET['isListStyle']));
    }

    /**
     * Adds the behavioral overlay to the basket and checkout pages.
     */
    private function addBehavioralOverlay(): void {
        if ($this->config->isUseBehavioralOverlay()
            && $this->config->isButtonPayActive()
            && (Shop::getPageType() === PAGE_WARENKORB || Shop::getPageType() === PAGE_BESTELLVORGANG)
            && $this->paymentMethodModule->isValid(Frontend::getCustomer(), Frontend::getCart())
        ) {
            $this->isBehavioralOverlayDisplay = true; // flag this down for the add buttons function
            $this->isResourcesRequired = true;
            Shop::Smarty()->assign('lpaBehavioralOverlay', [
                'title' => Translation::getInstance()->get(Translation::KEY_BEHAVIORAL_OVERLAY_TITLE),
                'text' => Translation::getInstance()->get(Translation::KEY_BEHAVIORAL_OVERLAY_TEXT)
            ]);
            pq('body')->append(Shop::Smarty()->fetch($this->getTemplateFilePath(self::TEMPLATE_ID_FRONTEND_SNIPPET_BEHAVIORAL_OVERLAY)));
        }
    }

    private function getEligibleListingProductIds(): array {
        $result = [];
        if ($this->isAjaxRequestForListingExpress()) {
            $product = Shop::Smarty()->getTemplateVars('Artikel');
            if (!empty($product) && $this->isExpressBuyable($product)) {
                $result[] = (int)$product->kArtikel;
            }
        } else {
            /** @var SearchResults $searchResults */
            $searchResults = Shop::Smarty()->getTemplateVars('Suchergebnisse');
            if (isset($searchResults) && null !== $searchResults->getProducts() && $searchResults->getProducts()->getIterator()->count() > 0) {
                foreach ($searchResults->getProducts()->getIterator() as $product) {
                    if ((int)$product->kArtikel > 0 && $this->isExpressBuyable($product)) {
                        $result[] = (int)$product->kArtikel;
                    }
                }
            }
            // bestsellers might be shown atop before the actual results, they cannot be found in the Suchergebnisse array
            $oBestseller_arr = Shop::Smarty()->getTemplateVars('oBestseller_arr');
            if (isset($oBestseller_arr) && \is_array($oBestseller_arr) && !empty($oBestseller_arr)) {
                foreach ($oBestseller_arr as $product) {
                    if ((int)$product->kArtikel > 0 && $this->isExpressBuyable($product)) {
                        $result[] = (int)$product->kArtikel;
                    }
                }
            }
        }
        return array_unique($result);
    }

    private function addCustomerSubscriptionsLink() {
        if(Shop::getPageType() === PAGE_MEINKONTO) {
            $subscriptionCustomerController = new SubscriptionCustomerController($this->plugin);
            $pqSelector = $this->config->getSubscriptionCustomerAccountPqSelector();
            $pqMethod = $this->config->getSubscriptionCustomerAccountPqMethod();
            if(empty($pqSelector) || empty($pqMethod)) {
                return;
            }
            if($subscriptionCustomerController->customerHasSubscriptions() && pq($pqSelector)->length > 0) {
                Shop::Smarty()->assign('lpaSubscriptionCustomerLink', [
                    'text' => Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE),
                    'link' => JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_SUBSCRIPTION_CUSTOMER),
                    'linkText' => Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE_LINK),
                ]);
                pq($pqSelector)->$pqMethod(Shop::Smarty()->fetch($this->getTemplateFilePath(self::TEMPLATE_ID_FRONTEND_SNIPPET_SUBSCRIPTION_CUSTOMER_SUBSCRIPTIONS_AVAILABLE)));
            }
        }
    }

}