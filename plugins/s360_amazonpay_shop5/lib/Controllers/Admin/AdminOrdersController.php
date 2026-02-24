<?php declare(strict_types=1);

namespace Plugin\s360_amazonpay_shop5\lib\Controllers\Admin;

use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;

class AdminOrdersController extends AdminController {

    use JtlLoggerTrait;

    public function handle(): string {
        $this->prepareSmartyVariables();
        return $this->finalize($this->plugin->getPaths()->getAdminPath() . 'template/orders.tpl');
    }

    private function prepareSmartyVariables() {
        $vars = [];
        $vars['formTargetUrl'] = JtlLinkHelper::getInstance()->getFullUrlForAdminTab(JtlLinkHelper::ADMIN_TAB_ORDERS);
        Shop::Smarty()->assign('lpaOrders', $vars);
    }
}