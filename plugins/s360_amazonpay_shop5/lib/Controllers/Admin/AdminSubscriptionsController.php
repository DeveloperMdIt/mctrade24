<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Controllers\Admin;

use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

class AdminSubscriptionsController extends AdminController {


    public function handle(): string {
        $this->prepareSmartyVariables();
        return $this->finalize($this->plugin->getPaths()->getAdminPath() . 'template/subscriptions_overview.tpl');
    }

    protected function prepareSmartyVariables() {
        $vars = [];
        $vars['formTargetUrl'] = JtlLinkHelper::getInstance()->getFullUrlForAdminTab(JtlLinkHelper::ADMIN_TAB_SUBSCRIPTION_OVERVIEW);
        Shop::Smarty()->assign('lpaSubscriptions', $vars);
    }

}