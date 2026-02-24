<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use JTL\Alert\Alert;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Entities\Subscription;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;

/**
 * This controller handles customer facing subscription logic.
 *
 * @class SubscriptionCustomerController
 */
class SubscriptionCustomerController {
    use JtlLoggerTrait;

    /** @var PluginInterface */
    protected $plugin;
    protected $config;
    protected $request;
    protected $subscriptionController;
    protected $customerId;

    public function __construct($plugin) {
        $this->request = $_REQUEST;
        $this->plugin = $plugin;
        $this->config = Config::getInstance();
        $this->subscriptionController = new SubscriptionController($this->plugin);
        $this->customerId = Frontend::getCustomer()->getID();
    }

    public function handle() {
        // Avoid ajax calls to this site altogether
        if (Request::isAjaxRequest()) {
            return;
        }
        if (!($this->customerId > 0)) {
            // No customer in the session - abort.
            return;
        }
        $this->handleSubmit();
        $this->handleDisplay();
    }

    /**
     * Handles potential submits to ourself.
     */
    protected function handleSubmit() {
        if(isset($this->request['action'])) {
            if(!Form::validateToken()) {
                $this->debugLog('CSRF Token invalid for customer action.', __CLASS__);
                return;
            }
            if($this->request['action'] === 'cancelSubscription') {
                if(!empty($this->request['subscriptionId']) && (int)$this->request['subscriptionId'] > 0) {
                    try {
                        $this->subscriptionController->cancelSubscriptionForCustomer($this->customerId, (int)$this->request['subscriptionId']);
                        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_CUSTOMER_CANCELED), 'lpaSubscriptionCustomerCanceled');
                    } catch(\Exception $ex) {
                        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_CUSTOMER_CANCEL_FAILED), 'lpaSubscriptionCustomerCanceled');
                    }
                }
            }
        }
    }

    /**
     * Handles display, i.e. Smarty variable assignments.
     *
     * Alerts will be displayed by the alert service.
     */
    protected function handleDisplay() {
        $subscriptions = $this->subscriptionController->getSubscriptionsForCustomer($this->customerId);
        $orders = [];
        foreach($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            $subscriptionId = $subscription->getId();
            if($subscriptionId === null) {
                continue;
            }
            $orders[$subscriptionId] = Database::getInstance()->getOrdersForSubscription($subscriptionId);
        }

        Shop::Smarty()->assign('lpaSubscription', [
            'subscriptions' => $subscriptions,
            'orders' => $orders,
            'subscriptionsActive' => $this->config->getSubscriptionMode() === Config::SUBSCRIPTION_MODE_ACTIVE,
            'translations' => $this->plugin->getLocalization()->getTranslations()
        ]);
    }

    public function customerHasSubscriptions(): bool {
        $subscriptions = $this->subscriptionController->getSubscriptionsForCustomer($this->customerId);
        return !empty($subscriptions);
    }

}