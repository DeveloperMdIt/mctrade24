<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Backend\StatusCheck\OrphanedCategories;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CategoryCheckController
 * @package JTL\Router\Controller\Backend
 */
class CategoryCheckController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::DIAGNOSTIC_VIEW);
        $this->getText->loadAdminLocale('pages/categorycheck');

        $orphanedCategories = (new OrphanedCategories($this->db, $this->cache, ''))->getOrphanedCategories();

        return $smarty->assign('passed', \count($orphanedCategories) === 0)
            ->assign('cateogries', $orphanedCategories)
            ->getResponse('categorycheck.tpl');
    }
}
