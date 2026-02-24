<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use Plugin\s360_clerk_shop5\src\Utils\Helpers;

final class AdminDashboardController extends Controller
{
    public const TABNAME = 'Dashboard';
    public const TEMPLATE = 'template/dashboard/details';

    public function handle(): string
    {
        $this->smarty->assign('s360_clerk_dashboard', [
            'helpers' => new Helpers($this->plugin),
            'adminUrl' =>  $this->plugin->getPaths()->getAdminURL(),
            'pluginVersion' =>  $this->plugin->getCurrentVersion(),
            'tabname' => self::TABNAME
        ]);

        return $this->smarty->fetch($this->getTemplate(self::TEMPLATE, self::TEMPLATE_TYPE_BACKEND));
    }

}
