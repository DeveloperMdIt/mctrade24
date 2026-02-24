<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use InvalidArgumentException;
use JTL\Backend\Permissions;
use JTL\CSV\Export;
use JTL\CSV\RedirectImporter;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\DataType;
use JTL\Pagination\Filter;
use JTL\Pagination\Operation;
use JTL\Pagination\Pagination;
use JTL\Redirect\DomainObjects\RedirectDomainObject;
use JTL\Redirect\Helpers\Normalizer;
use JTL\Redirect\Repositories\RedirectRefererRepository;
use JTL\Redirect\Repositories\RedirectRepository;
use JTL\Redirect\Services\RedirectService;
use JTL\Redirect\Type;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RedirectController
 * @package JTL\Router\Controller\Backend
 */
class RedirectController extends AbstractBackendController
{
    private RedirectService $service;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty->assign('currentRedirect');
        $this->checkPermissions(Permissions::REDIRECT_VIEW);
        $this->initData();

        return $smarty->assign('route', $this->route)
            ->assign('totalRedirectCount', $this->service->getTotalCount())
            ->getResponse('redirect.tpl');
    }

    public function initData(): void
    {
        $this->getText->loadAdminLocale('pages/redirect');
        $this->service = new RedirectService(
            new RedirectRepository($this->db),
            new RedirectRefererRepository($this->db),
            new Normalizer()
        );

        $action = Request::verifyGPDataString('importcsv') === 'redirects'
            ? 'csvImport'
            : Request::verifyGPDataString('action');
        $filter = $this->addFilter();

        $pagination = $this->addPagination($filter);

        $this->handleAction($action, $filter, $pagination);
        $this->addList($filter, $pagination);
    }

    private function handleAction(string $action, Filter $filter, Pagination $pagination): void
    {
        if (!Form::validateToken()) {
            return;
        }
        switch ($action) {
            case 'csvImport':
                $this->actionImport();
                break;
            case 'csvExport':
                $this->actionExport($filter, $pagination);
                break;
            case 'save':
                $this->actionSave();
                break;
            case 'delete':
                $this->actionDelete();
                break;
            case 'delete_all':
                $this->service->deleteUnassigned();
                break;
            case 'new':
                $this->createOrUpdate();
                break;
            case 'edit':
                $this->actionEdit();
                break;
            default:
                break;
        }
    }

    private function createOrUpdate(): void
    {
        if (Request::pInt('redirect-id') > 0) {
            $this->actionUpdate();

            return;
        }
        $this->actionCreate();
    }

    private function actionExport(Filter $filter, Pagination $pagination): void
    {
        $redirectCount = $this->service->getTotalCount($filter->getWhereSQL());
        $pagination->setItemCount($redirectCount)->assemble();
        $export = new Export();
        $export->export(
            'redirects',
            'redirects.csv',
            function () use ($filter, $pagination, $redirectCount) {
                $where = $filter->getWhereSQL();
                $order = $pagination->getOrderSQL();
                for ($i = 0; $i < $redirectCount; $i += 1000) {
                    $iter = $this->db->getPDOStatement(
                        'SELECT cFromUrl, cToUrl
                            FROM tredirect'
                        . ($where !== '' ? ' WHERE ' . $where : '')
                        . ($order !== '' ? ' ORDER BY ' . $order : '')
                        . ' LIMIT ' . $i . ', 1000'
                    );

                    foreach ($iter as $redirect) {
                        yield (object)$redirect;
                    }
                }
            }
        );
    }

    private function actionImport(): void
    {
        $importer = new RedirectImporter($this->db);
        if ($importer->runImport() === false) {
            $this->alertService->addError(
                \__('errorImport') . '<br><br>' . \implode('<br>', $importer->getErrors()),
                'errorImport'
            );
        } else {
            $this->alertService->addSuccess(\__('successImport'), 'successImport');
        }
    }

    private function actionSave(): void
    {
        foreach ($_POST['redirects'] ?? [] as $id => $item) {
            try {
                $redirect = $this->service->getByID((int)$id);
            } catch (InvalidArgumentException) {
                continue;
            }
            if ($redirect->id <= 0 || $redirect->destination === $item['cToUrl']) {
                continue;
            }
            if (isset($item['cFromUrl'])) {
                $redirect->source = $item['cFromUrl'];
            }
            $redirect->destination = $item['cToUrl'];
            $redirect->available   = 'y';
            if (isset($item['paramHandling'])) {
                $redirect->paramHandling = (int)$item['paramHandling'];
            }
            if (!$this->service->update($redirect)) {
                $this->alertService->addError(
                    \sprintf(\__('errorURLNotReachable'), $item['cToUrl']),
                    'errorURLNotReachable'
                );
            }
        }
    }

    private function actionEdit(): void
    {
        $redirect = $this->service->getByID(Request::gInt('id'));
        $this->getSmarty()->assign('cTab', 'new_redirect')
            ->assign('currentRedirect', $redirect)
            ->assign('redirectID', $redirect->id)
            ->assign('nCount', $redirect->count)
            ->assign('paramHandling', $redirect->paramHandling);
    }

    private function actionCreate(): void
    {
        $redirect = $this->service->createDO(
            Request::verifyGPDataString('cFromUrl'),
            Request::verifyGPDataString('cToUrl'),
            Request::verifyGPCDataInt('paramHandling'),
            Type::MANUAL
        );
        if ($this->service->save($redirect) > 0) {
            $this->alertService->addSuccess(\__('successRedirectSave'), 'successRedirectSave');
        } else {
            $this->alertService->addError(\__('errorCheckInput'), 'errorCheckInput');
            $this->getSmarty()->assign('cTab', 'new_redirect')
                ->assign('cFromUrl', Text::filterXSS(Request::verifyGPDataString('cFromUrl')))
                ->assign('cToUrl', Text::filterXSS(Request::verifyGPDataString('cToUrl')));
        }
    }

    private function actionUpdate(): void
    {
        $redirect = new RedirectDomainObject(
            source: Request::pString('cFromUrl'),
            destination: Request::pString('cToUrl'),
            id: Request::pInt('redirect-id'),
            paramHandling: Request::pInt('paramHandling')
        );
        if ($this->service->update($redirect) === true) {
            $this->alertService->addSuccess(\__('successRedirectSave'), 'successRedirectSave');
            return;
        }
        $this->alertService->addError(
            \sprintf(\__('errorURLNotReachable'), Request::pString('cToUrl')),
            'errorURLNotReachable'
        );
    }

    private function actionDelete(): void
    {
        $count = 0;
        foreach ($_POST['redirects'] ?? [] as $id => $item) {
            if (((int)($item['enabled'] ?? '0') === 1) && $this->service->deleteByID((int)$id)) {
                ++$count;
            }
        }
        if ($count > 0) {
            $this->alertService->addSuccess(\sprintf(\__('%d items deleted.'), $count), 'successRedirectDelete');
            return;
        }
        $this->alertService->addError(\__('Please select at least one item to delete.'), 'errorDelete');
    }

    private function addFilter(): Filter
    {
        $filter = new Filter('redirectFilter');
        $filter->addTextfield(\__('redirectFrom'), 'cFromUrl', Operation::CONTAINS, DataType::TEXT, 'rdfrom');
        $filter->addTextfield(\__('redirectTo'), 'cToUrl', Operation::CONTAINS, DataType::TEXT, 'rdto');
        $select = $filter->addSelectfield(\__('redirection'), 'cToUrl', 0, 'redirect');
        $select->addSelectOption(\__('all'), '');
        $select->addSelectOption(\__('available'), '', Operation::NOT_EQUAL);
        $select->addSelectOption(\__('missing'), '', Operation::EQUALS);
        $type = $filter->addSelectfield(\__('Type'), 'type', 0, 'rdtype');
        $type->addSelectOption(\__('all'), -1);
        $type->addSelectOption(\__('Manual'), Type::MANUAL, Operation::EQUALS);
        $type->addSelectOption(\__('Import'), Type::IMPORT, Operation::EQUALS);
        $type->addSelectOption(\__('Wawi sync'), Type::WAWI, Operation::EQUALS);
        $type->addSelectOption(\__('Not found'), Type::NOTFOUND, Operation::EQUALS);
        $type->addSelectOption(\__('Unknown'), Type::UNKNOWN, Operation::EQUALS);
        $filter->addTextfield(\__('calls'), 'nCount', Operation::CUSTOM, DataType::NUMBER, 'rdcount');
        $filter->addDaterangefield(\__('Date created'), 'dateCreated', '', 'rdcreated');
        $filter->assemble();
        $this->getSmarty()->assign('oFilter', $filter);

        return $filter;
    }

    private function addPagination(Filter $filter): Pagination
    {
        $pagination = new Pagination();
        $pagination->setSortByOptions([
            ['cFromUrl', \__('redirectFrom')],
            ['cToUrl', \__('redirectTo')],
            ['nCount', \__('calls')],
            ['type', \__('Type')]
        ]);
        $pagination->setItemCount($this->service->getTotalCount($filter->getWhereSQL()))->assemble();
        $this->getSmarty()->assign('pagination', $pagination);

        return $pagination;
    }

    private function addList(Filter $filter, Pagination $pagination): void
    {
        $pagination->setItemCount($this->service->getTotalCount($filter->getWhereSQL()))->assemble();
        $this->getSmarty()->assign(
            'redirects',
            $this->service->getRedirects(
                $filter->getWhereSQL(),
                $pagination->getOrderSQL(),
                $pagination->getLimitSQL()
            )
        );
    }
}
