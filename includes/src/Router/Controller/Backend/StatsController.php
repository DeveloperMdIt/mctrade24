<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Crawler\Controller;
use JTL\Crawler\Crawler;
use JTL\Helpers\Request;
use JTL\Linechart;
use JTL\Pagination\Filter;
use JTL\Pagination\Pagination;
use JTL\Piechart;
use JTL\Smarty\JTLSmarty;
use JTL\Statistik;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class StatsController
 * @package JTL\Router\Controller\Backend
 */
class StatsController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->getText->loadAdminLocale('pages/statistik');
        $statsType = (int)($args['id'] ?? Request::verifyGPCDataInt('s'));
        $crawler   = null;
        if ($statsType === 0) {
            $statsType = \STATS_ADMIN_TYPE_BESUCHER;
        }
        $this->checkPermissions($this->getPermissionByStatsType($statsType));
        $this->route = \str_replace('[/{id}]', '/' . $statsType, $this->route);

        $filter = new Filter('statistics');
        $filter->assemble();

        $backendStats = $this->getBackendStats($statsType, $filter);
        $stats        = $backendStats;
        if ($statsType === \STATS_ADMIN_TYPE_CONSENT) {
            $stats = $backendStats['dataChart'];
        }
        $this->handleCharts($statsType, $stats);
        if ($statsType === \STATS_ADMIN_TYPE_CONSENT) {
            $stats = $backendStats['dataTable'];
        } elseif ($statsType === \STATS_ADMIN_TYPE_SUCHMASCHINE) {
            $crawler = $this->handleCrawlerStats();
        }
        $smarty->assign('route', $this->route);

        if ($statsType === \STATS_ADMIN_TYPE_SUCHMASCHINE && \is_object($crawler)) {
            return $smarty->assign('crawler', $crawler)
                ->getResponse('tpl_inc/crawler_edit.tpl');
        }
        $members = [];
        foreach ($stats as $stat) {
            $members[] = \array_keys(\get_object_vars($stat));
        }

        $pagination = (new Pagination())
            ->setItemCount(\count($stats))
            ->assemble();

        return $smarty->assign('nTyp', $statsType)
            ->assign('oStat_arr', $stats)
            ->assign('cMember_arr', $this->mapData($members, $this->getMappingByType($statsType)))
            ->assign('nPosAb', $pagination->getFirstPageItem())
            ->assign('nPosBis', $pagination->getFirstPageItem() + $pagination->getPageItemCount())
            ->assign('pagination', $pagination)
            ->assign('oFilter', $filter)
            ->getResponse('statistik.tpl');
    }

    /**
     * @return array{}|array<int, object{cEinstiegsseite: string, nCount: int}|object{cReferer: string,
     *      nCount: int}|object{cUserAgent: string, nCount: int}|object{dZeit: string, nCount: int}>
     */
    private function getBackendStats(int $type, Filter $filter): array
    {
        $dateRange = $filter->addDaterangefield(
            \__('Zeitraum'),
            '',
            \date_create()->modify('-1 year')->modify('+1 day')->format('d.m.Y') . ' - ' . \date('d.m.Y'),
            'date'
        );
        $from      = \strtotime($dateRange->getStart()) ?: 0;
        $to        = \strtotime($dateRange->getEnd()) ?: 0;
        if ($type <= 0 || $from <= 0 || $to <= 0) {
            return [];
        }
        $stats = new Statistik($from, $to);
        $stats->getAnzeigeIntervall();

        return match ($type) {
            \STATS_ADMIN_TYPE_BESUCHER        => $stats->holeBesucherStats(),
            \STATS_ADMIN_TYPE_KUNDENHERKUNFT  => $stats->holeKundenherkunftStats(),
            \STATS_ADMIN_TYPE_SUCHMASCHINE    => $stats->holeBotStats(),
            \STATS_ADMIN_TYPE_UMSATZ          => $stats->holeUmsatzStats(),
            \STATS_ADMIN_TYPE_EINSTIEGSSEITEN => $stats->holeEinstiegsseiten(),
            \STATS_ADMIN_TYPE_CONSENT         => $stats->getConsentStats(),
            default                           => [],
        };
    }

    /**
     * @return array{nCount: string, dZeit: string,
     *      cReferer?: string, cUserAgent?: string, cEinstiegsseite?: string}|array{}
     */
    private function getMappingByType(int $type): array
    {
        $mapping = [
            \STATS_ADMIN_TYPE_BESUCHER        => [
                'nCount' => \__('count'),
                'dZeit'  => \__('date')
            ],
            \STATS_ADMIN_TYPE_KUNDENHERKUNFT  => [
                'nCount'   => \__('count'),
                'dZeit'    => \__('date'),
                'cReferer' => \__('origin')
            ],
            \STATS_ADMIN_TYPE_SUCHMASCHINE    => [
                'nCount'     => \__('count'),
                'dZeit'      => \__('date'),
                'cUserAgent' => \__('userAgent')
            ],
            \STATS_ADMIN_TYPE_UMSATZ          => [
                'nCount' => \__('amount'),
                'dZeit'  => \__('date')
            ],
            \STATS_ADMIN_TYPE_EINSTIEGSSEITEN => [
                'nCount'          => \__('count'),
                'dZeit'           => \__('date'),
                'cEinstiegsseite' => \__('entryPage')
            ],
            \STATS_ADMIN_TYPE_CONSENT         => [
                'date'       => \__('date'),
                'visitors'   => \__('visitors'),
                'acceptance' => \__('consentAcceptedAll'),
                'consents'   => \__('consentDetails')
            ]
        ];

        return $mapping[$type] ?? [];
    }

    private function geNameByType(int $type): string
    {
        $names = [
            1 => \__('visitor'),
            2 => \__('customerHeritage'),
            3 => \__('searchEngines'),
            4 => \__('sales'),
            5 => \__('entryPages'),
            6 => \__('consent'),
        ];

        return $names[$type] ?? '';
    }

    private function getAxisNames(int $type): stdClass
    {
        $axis    = new stdClass();
        $axis->y = match ($type) {
            \STATS_ADMIN_TYPE_CONSENT => 'acceptance',
            default                   => 'nCount'
        };
        $axis->x = match ($type) {
            \STATS_ADMIN_TYPE_KUNDENHERKUNFT  => 'cReferer',
            \STATS_ADMIN_TYPE_SUCHMASCHINE    => 'cUserAgent',
            \STATS_ADMIN_TYPE_EINSTIEGSSEITEN => 'cEinstiegsseite',
            \STATS_ADMIN_TYPE_CONSENT         => 'date',
            default                           => 'dZeit',
        };

        return $axis;
    }

    /**
     * @param array<int|string, array<int|string, mixed>> $members
     * @param array<string, string>                       $mapping
     * @return array<int|string, array<int|string, mixed>>
     * @former mappeDatenMember()
     */
    private function mapData(array $members, array $mapping): array
    {
        foreach ($members as $i => $data) {
            foreach ($data as $j => $member) {
                $members[$i][$j]    = [];
                $members[$i][$j][0] = $member;
                $members[$i][$j][1] = $mapping[$member];
            }
        }

        return $members;
    }

    /**
     * @param array<int, object{dZeit: string, nCount: int, cReferer?: string, cUserAgent?: string,
     *       cEinstiegsseite?: string, nUmsatz?: float, nAnzahl?: int}> $stats
     */
    private function prepareLineChartStats(array $stats, string $name, stdClass $axis): Linechart
    {
        $chart = new Linechart(['active' => false]);

        if (\count($stats) === 0) {
            return $chart;
        }
        $chart->setActive(true);
        $data = [];
        $y    = $axis->y;
        $x    = $axis->x;
        foreach ($stats as $stat) {
            $obj    = new stdClass();
            $obj->y = \round((float)$stat->$y, 2, 1);
            $chart->addAxis((string)$stat->$x);

            $data[] = $obj;
        }

        $chart->addSerie($name, $data);
        $chart->memberToJSON();

        return $chart;
    }

    /**
     * @param array<int, object{dZeit: string, nCount: int, cReferer?: string, cUserAgent?: string,
     *       cEinstiegsseite?: string, nUmsatz?: float, nAnzahl?: int}> $stats
     */
    private function preparePieChartStats(array $stats, string $name, stdClass $axis): Piechart
    {
        $maxItems = 6;
        $chart    = new Piechart(['active' => false]);
        if (\count($stats) === 0) {
            return $chart;
        }
        $chart->setActive(true);
        $data = [];
        $y    = $axis->y;
        $x    = $axis->x;
        if (\count($stats) > $maxItems) {
            $statstmp  = [];
            $other     = new stdClass();
            $other->$y = 0;
            $other->$x = \__('miscellaneous');
            foreach ($stats as $i => $stat) {
                if ($i < $maxItems) {
                    $statstmp[] = $stat;
                } else {
                    $other->$y += $stat->$y;
                }
            }
            $statstmp[] = $other;
            $stats      = $statstmp;
        }

        foreach ($stats as $stat) {
            $value  = \round((float)$stat->$y, 2, 1);
            $data[] = [$stat->$x, $value];
        }
        $chart->addSerie($name, $data);
        $chart->memberToJSON();

        return $chart;
    }

    private function getPermissionByStatsType(int $statsType): string
    {
        return match ($statsType) {
            \STATS_ADMIN_TYPE_KUNDENHERKUNFT  => Permissions::STATS_VISITOR_LOCATION_VIEW,
            \STATS_ADMIN_TYPE_SUCHMASCHINE    => Permissions::STATS_CRAWLER_VIEW,
            \STATS_ADMIN_TYPE_UMSATZ          => Permissions::STATS_EXCHANGE_VIEW,
            \STATS_ADMIN_TYPE_EINSTIEGSSEITEN => Permissions::STATS_LANDINGPAGES_VIEW,
            \STATS_ADMIN_TYPE_CONSENT         => Permissions::STATS_CONSENT_VIEW,
            default                           => Permissions::STATS_VISITOR_VIEW,
        };
    }

    private function handleCrawlerStats(): Crawler|false
    {
        $controller = new Controller($this->db, $this->cache, $this->alertService);
        if (($crawler = $controller->checkRequest()) === false) {
            $crawlerPagination = (new Pagination('crawler'))
                ->setItemArray($controller->getAllCrawlers())
                ->assemble();
            $this->getSmarty()->assign('crawler_arr', $crawlerPagination->getPageItems())
                ->assign('crawlerPagination', $crawlerPagination);
        }

        return $crawler;
    }

    private function handleCharts(int $statsType, array $stats): void
    {
        $statsTypeName = $this->geNameByType($statsType);
        $axisNames     = $this->getAxisNames($statsType);
        $pie           = [
            \STATS_ADMIN_TYPE_KUNDENHERKUNFT,
            \STATS_ADMIN_TYPE_SUCHMASCHINE,
            \STATS_ADMIN_TYPE_EINSTIEGSSEITEN
        ];
        $this->getSmarty()->assign('headline', $statsTypeName);
        if (\in_array($statsType, $pie, true)) {
            $this->getSmarty()->assign('piechart', $this->preparePieChartStats($stats, $statsTypeName, $axisNames));
        } else {
            $members = $this->getMappingByType($statsType);
            $this->getSmarty()->assign('linechart', $this->prepareLineChartStats($stats, $statsTypeName, $axisNames))
                ->assign('ymax', $statsType === \STATS_ADMIN_TYPE_CONSENT ? '100' : '')
                ->assign('ymin', '0')
                ->assign('yunit', $statsType === \STATS_ADMIN_TYPE_CONSENT ? ' in %' : '')
                ->assign(
                    'ylabel',
                    $statsType === \STATS_ADMIN_TYPE_CONSENT ? $members['acceptance'] : ($members['nCount'] ?? 0)
                );
        }
    }
}
