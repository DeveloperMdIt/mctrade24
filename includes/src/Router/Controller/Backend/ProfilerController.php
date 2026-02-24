<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Profiler;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ProfilerController
 * @package JTL\Router\Controller\Backend
 */
class ProfilerController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::PROFILER_VIEW);
        $this->getText->loadAdminLocale('pages/profiler');
        $this->handlePostData();

        return $smarty->assign('tab', Request::postVar('tab', 'uebersicht'))
            ->assign('route', $this->route)
            ->assign('sqlProfilerData', Profiler::getSQLProfiles())
            ->getResponse('profiler.tpl');
    }

    private function handlePostData(): void
    {
        if (!isset($_POST['delete-run-submit']) || !Form::validateToken()) {
            return;
        }
        if (\is_numeric(Request::postVar('run-id'))) {
            if ($this->deleteProfileRun(false, Request::pInt('run-id')) > 0) {
                $this->alertService->addSuccess(\__('successEntryDelete'), 'successEntryDelete');
            } else {
                $this->alertService->addError(\__('errorEntryDelete'), 'errorEntryDelete');
            }
        } elseif (Request::postVar('delete-all') === 'y') {
            if ($this->deleteProfileRun(true) > 0) {
                $this->alertService->addSuccess(\__('successEntriesDelete'), 'successEntriesDelete');
            } else {
                $this->alertService->addError(\__('errorEntriesDelete'), 'errorEntriesDelete');
            }
        }
    }

    private function deleteProfileRun(bool $all = false, int $runID = 0): int
    {
        if ($all === true) {
            $count = $this->db->getAffectedRows('DELETE FROM tprofiler');
            $this->db->query('ALTER TABLE tprofiler AUTO_INCREMENT = 1');
            $this->db->query('ALTER TABLE tprofiler_runs AUTO_INCREMENT = 1');

            return $count;
        }

        return $this->db->delete('tprofiler', 'runID', $runID);
    }
}
