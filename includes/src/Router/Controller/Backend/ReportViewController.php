<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use Exception;
use InvalidArgumentException;
use JTL\Exceptions\PermissionException;
use JTL\Helpers\Request;
use JTL\ServiceReport\ReportService;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ReportController
 * @package JTL\Router\Controller\Backend
 */
class ReportViewController extends ReportController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        if (!isset($args['id'])) {
            return $this->smarty->getResponse('404.tpl');
        }
        $this->service = new ReportService(Shop::Container()->getPasswordService());
        $ext           = '.html';
        if (isset($args['extension'])) {
            $ext = '.' . $args['extension'];
        }
        try {
            return $this->download($this->validateReport((string)$args['id']), $ext);
        } catch (InvalidArgumentException) {
            return $this->smarty->getResponse('404.tpl');
        }
    }

    private function validateReport(string $hash): int
    {
        try {
            $report = $this->service->getReportByHash($hash);
        } catch (Exception) {
            throw new PermissionException(\__('Report is not available'));
        }
        if ($report->visited !== null || $report->validUntil < \date('Y-m-d H:i:s')) {
            throw new PermissionException(\__('Report is not available'));
        }
        $id     = (int)$report->id;
        $update = (object)[
            'validUntil' => 'NOW()',
            'visited'    => 'NOW()',
            'remoteIP'   => Request::getRealIP()
        ];
        $this->service->updateReportByID($id, $update);

        return $id;
    }
}
