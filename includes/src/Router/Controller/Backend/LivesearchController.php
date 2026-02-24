<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\DB\SqlObject;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class LivesearchController
 * @package JTL\Router\Controller\Backend
 */
class LivesearchController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::MODULE_LIVESEARCH_VIEW);
        $this->getText->loadAdminLocale('pages/livesuche');
        $this->setLanguage();

        $settingsIDs = [
            'livesuche_max_ip_count',
            'sonstiges_livesuche_all_top_count',
            'sonstiges_livesuche_all_last_count',
            'boxen_livesuche_count',
            'boxen_livesuche_anzeigen'
        ];
        if (Request::verifyGPCDataInt('einstellungen') === 1) {
            $this->alertService->addSuccess(
                $this->saveAdminSettings($settingsIDs, $_POST, [\CACHING_GROUP_OPTION], true),
                'saveSettings'
            );
            $this->getSmarty()->assign('tab', 'einstellungen');
        }
        if (Request::pInt('livesuche') === 1) {
            if (Request::pInt('suchanfragenUpdate') === 1) {
                $this->actionUpdate($this->currentLanguageID);
            } elseif (Request::pInt('submitMapping') === 1) {
                $this->actionMap($this->currentLanguageID);
            } elseif (Request::pInt('delete') === 1) {
                $this->actionDelete(Request::verifyGPDataIntegerArray('kSuchanfrage'));
            }
        } elseif (Request::pInt('livesuche') === 2) {
            $this->actionMapWithoutSuccess($this->currentLanguageID);
            $this->getSmarty()->assign('tab', 'erfolglos');
        } elseif (Request::pInt('livesuche') === 3) {
            $this->actionBlacklist($this->currentLanguageID);
        } elseif (Request::pInt('livesuche') === 4) {
            if (Request::pInt('delete') === 1) {
                $this->actionDeleteMapping(Request::pIntArray('kSuchanfrageMapping'));
            }
            $this->getSmarty()->assign('tab', 'mapping');
        }
        $this->assignData($this->currentLanguageID);
        $this->getAdminSectionSettings($settingsIDs, true);

        return $this->getSmarty()->assign('route', $this->route)
            ->getResponse('livesuche.tpl');
    }

    /**
     * @param int[] $queryIDs
     */
    private function actionDelete(array $queryIDs): void
    {
        if (\count($queryIDs) === 0) {
            $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            return;
        }
        foreach ($queryIDs as $queryID) {
            $data = $this->db->select(
                'tsuchanfrage',
                'kSuchanfrage',
                $queryID
            );
            if ($data === null) {
                continue;
            }
            $obj           = new stdClass();
            $obj->kSprache = (int)$data->kSprache;
            $obj->cSuche   = $data->cSuche;

            $this->db->delete('tsuchanfrage', 'kSuchanfrage', $queryID);
            $this->db->insert('tsuchanfrageblacklist', $obj);
            // Aus tseo loeschen
            $this->db->delete('tseo', ['cKey', 'kKey'], ['kSuchanfrage', $queryID]);
            $this->alertService->addSuccess(
                \sprintf(\__('successSearchDelete'), $data->cSuche),
                'sucSearchDelete'
            );
            $this->alertService->addSuccess(
                \sprintf(\__('successSearchBlacklist'), $data->cSuche),
                'sucSearchBlacklist'
            );
        }
    }

    private function actionMap(int $languageID): void
    {
        $mapping = Request::verifyGPDataString('cMapping');
        if (\mb_strlen($mapping) === 0) {
            $this->alertService->addError(\__('errorMapNameMissing'), 'errorMapNameMissing');
            return;
        }
        $mappingQueryIDs = Request::verifyGPDataIntegerArray('kSuchanfrage');
        if (\count($mappingQueryIDs) === 0) {
            $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
            return;
        }
        foreach ($mappingQueryIDs as $searchQueryID) {
            $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $searchQueryID);
            if ($query === null || $query->kSuchanfrage <= 0) {
                $this->alertService->addError(\__('errorSearchMapNotExist'), 'errorSearchMapNotExist');
                return;
            }
            if (\mb_convert_case($query->cSuche, \MB_CASE_LOWER) === \mb_convert_case($mapping, \MB_CASE_LOWER)) {
                $this->alertService->addError(\__('errorSearchMapSelf'), 'errorSearchMapSelf');
                return;
            }
            $mappedSearch = $this->db->getSingleObject(
                'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                    FROM tsuchanfrage
                    WHERE cSuche = :cSuche',
                [
                    'cSuche' => $mapping,
                    'mapped' => $query->cSuche,
                ]
            );
            if ($mappedSearch === null || (int)($mappedSearch->isEqual ?? 0) !== 0) {
                if ((int)($mappedSearch->isEqual ?? 0) === 1) {
                    $this->alertService->addError(
                        \sprintf(\__('errorSearchMapLoop'), $query->cSuche, $mapping),
                        'errorSearchMapToNotExist'
                    );
                } else {
                    $this->alertService->addError(
                        \__('errorSearchMapToNotExist'),
                        'errorSearchMapToNotExist'
                    );
                }
                return;
            }
            $queryMapping                 = new stdClass();
            $queryMapping->kSprache       = $languageID;
            $queryMapping->cSuche         = $query->cSuche;
            $queryMapping->cSucheNeu      = $mapping;
            $queryMapping->nAnzahlGesuche = $query->nAnzahlGesuche;

            $mappingID = $this->db->insert('tsuchanfragemapping', $queryMapping);
            if ($mappingID > 0) {
                $this->db->queryPrepared(
                    'UPDATE tsuchanfrage
                        SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                        WHERE kSprache = :lid
                            AND kSuchanfrage = :sid',
                    [
                        'cnt' => $query->nAnzahlGesuche,
                        'lid' => $languageID,
                        'sid' => $mappedSearch->kSuchanfrage
                    ]
                );
                $this->db->delete('tsuchanfrage', 'kSuchanfrage', (int)$query->kSuchanfrage);
                $this->db->queryPrepared(
                    "UPDATE tseo
                        SET kKey = :kid
                        WHERE cKey = 'kSuchanfrage'
                            AND kKey = :sid",
                    [
                        'kid' => (int)$mappedSearch->kSuchanfrage,
                        'sid' => (int)$query->kSuchanfrage
                    ]
                );

                $this->alertService->addSuccess(
                    \sprintf(\__('successSearchMapMultiple'), $queryMapping->cSucheNeu),
                    'successSearchMapMultiple'
                );
            }
        }
    }

    private function actionMapWithoutSuccess(int $languageID): void
    {
        if (Request::pInt('erfolglosEdit') === 1) {
            $this->getSmarty()->assign('nErfolglosEditieren', 1);
            return;
        }
        if (Request::pInt('erfolglosUpdate') === 1) {
            $this->actionUpdateWithoutSuccess($languageID);
            return;
        }
        if (Request::pInt('erfolglosDelete') === 1) {
            $queryIDs = Request::pIntArray('kSuchanfrageErfolglos');
            if (\count($queryIDs) === 0) {
                $this->alertService->addError(\__('errorAtLeastOneSearch'), 'errorAtLeastOneSearch');
                return;
            }
            foreach ($queryIDs as $queryID) {
                $this->db->delete('tsuchanfrageerfolglos', 'kSuchanfrageErfolglos', $queryID);
            }
            $this->alertService->addSuccess(\__('successSearchDeleteMultiple'), 'successSearchDeleteMultiple');
        }
    }

    private function actionUpdateWithoutSuccess(int $languageID): void
    {
        $failedQueries = $this->db->selectAll(
            'tsuchanfrageerfolglos',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        foreach ($failedQueries as $failedQuery) {
            $idx = 'mapping_' . $failedQuery->kSuchanfrageErfolglos;
            if (\mb_strlen(Request::pString($idx)) > 0) {
                if (
                    \mb_convert_case($failedQuery->cSuche, \MB_CASE_LOWER) !==
                    \mb_convert_case(Request::pString($idx), \MB_CASE_LOWER)
                ) {
                    $mapping                 = new stdClass();
                    $mapping->kSprache       = $languageID;
                    $mapping->cSuche         = $failedQuery->cSuche;
                    $mapping->cSucheNeu      = Request::pString($idx);
                    $mapping->nAnzahlGesuche = $failedQuery->nAnzahlGesuche;

                    $oldQuery = $this->db->getSingleObject(
                        'SELECT tsuchanfrageerfolglos.kSuchanfrageErfolglos, IF(:mapped = :cSuche, 1, 0) isEqual
                            FROM tsuchanfrageerfolglos
                            WHERE cSuche = :cSuche',
                        [
                            'cSuche' => $mapping->cSuche,
                            'mapped' => $mapping->cSucheNeu,
                        ]
                    );
                    // check if loops would be created with mapping
                    $bIsLoop           = (int)($oldQuery->isEqual ?? 0) > 0;
                    $sSearchMappingTMP = $mapping->cSucheNeu;
                    while (!empty($sSearchMappingTMP) && !$bIsLoop) {
                        $oSearchMappingNextTMP = $this->db->getSingleObject(
                            'SELECT tsuchanfragemapping.cSucheNeu,
                            IF(:mapped = tsuchanfragemapping.cSucheNeu, 1, 0) isEqual
                                FROM tsuchanfragemapping
                                WHERE tsuchanfragemapping.cSuche = :cSuche
                                    AND tsuchanfragemapping.kSprache = :languageID',
                            [
                                'languageID' => $languageID,
                                'cSuche'     => $sSearchMappingTMP,
                                'mapped'     => $mapping->cSuche,
                            ]
                        );
                        if ((int)($oSearchMappingNextTMP->isEqual ?? 0) === 1) {
                            $bIsLoop = true;
                            break;
                        }
                        if ($oSearchMappingNextTMP !== null && !empty($oSearchMappingNextTMP->cSucheNeu)) {
                            $sSearchMappingTMP = $oSearchMappingNextTMP->cSucheNeu;
                        } else {
                            $sSearchMappingTMP = null;
                        }
                    }

                    if (!$bIsLoop) {
                        if ($oldQuery !== null && $oldQuery->kSuchanfrageErfolglos > 0) {
                            $this->db->insert('tsuchanfragemapping', $mapping);
                            $this->db->delete(
                                'tsuchanfrageerfolglos',
                                'kSuchanfrageErfolglos',
                                (int)$oldQuery->kSuchanfrageErfolglos
                            );

                            $this->alertService->addSuccess(
                                \sprintf(
                                    \__('successSearchMap'),
                                    $mapping->cSuche,
                                    $mapping->cSucheNeu
                                ),
                                'successSearchMap'
                            );
                        }
                    } else {
                        $this->alertService->addError(
                            \sprintf(
                                \__('errorSearchMapLoop'),
                                $mapping->cSuche,
                                $mapping->cSucheNeu
                            ),
                            'errorSearchMapLoop'
                        );
                    }
                } else {
                    $this->alertService->addError(
                        \sprintf(\__('errorSearchMapSelf'), $failedQuery->cSuche),
                        'errSearchMapSelf'
                    );
                }
            } elseif (Request::pInt('nErfolglosEditieren') === 1) {
                $idx                 = 'cSuche_' . $failedQuery->kSuchanfrageErfolglos;
                $failedQuery->cSuche = Text::filterXSS(Request::pString($idx));
                $upd                 = (object)['cSuche' => $failedQuery->cSuche];
                $this->db->update(
                    'tsuchanfrageerfolglos',
                    'kSuchanfrageErfolglos',
                    (int)$failedQuery->kSuchanfrageErfolglos,
                    $upd
                );
            }
        }
    }

    private function actionBlacklist(int $languageID): void
    {
        $this->db->delete('tsuchanfrageblacklist', 'kSprache', $languageID);
        foreach (\explode(';', Request::pString('suchanfrageblacklist')) as $item) {
            if (!empty($item)) {
                $ins           = new stdClass();
                $ins->cSuche   = $item;
                $ins->kSprache = $languageID;
                $this->db->insert('tsuchanfrageblacklist', $ins);
            }
        }
        $this->getSmarty()->assign('tab', 'blacklist');
        $this->alertService->addSuccess(\__('successBlacklistRefresh'), 'successBlacklistRefresh');
    }

    /**
     * @param int[] $mappingIDs
     */
    private function actionDeleteMapping(array $mappingIDs): void
    {
        if (\count($mappingIDs) === 0) {
            $this->alertService->addError(\__('errorAtLeastOneSearchMap'), 'errorAtLeastOneSearchMap');
            return;
        }
        foreach ($mappingIDs as $mappingID) {
            $queryMapping = $this->db->select('tsuchanfragemapping', 'kSuchanfrageMapping', $mappingID);
            if ($queryMapping !== null && \mb_strlen($queryMapping->cSuche) > 0) {
                $this->db->delete(
                    'tsuchanfragemapping',
                    'kSuchanfrageMapping',
                    $mappingID
                );
                $this->alertService->addSuccess(
                    \sprintf(\__('successSearchMapDelete'), $queryMapping->cSuche),
                    'successSearchMapDelete'
                );
            } else {
                $this->alertService->addError(
                    \sprintf(\__('errorSearchMapNotFound'), $mappingID),
                    'errSearchMapNotFound'
                );
            }
        }
    }

    private function actionUpdate(int $languageID): void
    {
        $searchQueryIDs = Request::pIntArray('kSuchanfrageAll');
        foreach ($searchQueryIDs as $searchQueryID) {
            $value = Request::pInt('nAnzahlGesuche_' . $searchQueryID);
            if ($value > 0) {
                $upd = (object)['nAnzahlGesuche' => $value];
                $this->db->update('tsuchanfrage', 'kSuchanfrage', $searchQueryID, $upd);
            }
        }
        $searchQueries = $this->db->selectAll(
            'tsuchanfrage',
            'kSprache',
            $languageID,
            '*',
            'nAnzahlGesuche DESC'
        );
        // Wurde ein Mapping durchgefuehrt
        if (\count($searchQueryIDs) > 0) {
            $whereIn   = ' IN (';
            $deleteIDs = [];
            // nAktiv Reihe updaten
            foreach ($searchQueryIDs as $searchQueryID) {
                $this->db->update('tsuchanfrage', 'kSuchanfrage', $searchQueryID, (object)['nAktiv' => 0]);
                $deleteIDs[] = $searchQueryID;
            }
            $whereIn .= \implode(',', $deleteIDs);
            $whereIn .= ')';
            // Deaktivierte Suchanfragen aus tseo loeschen
            $this->db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kSuchanfrage'
                    AND kKey" . $whereIn
            );
            // Deaktivierte Suchanfragen in tsuchanfrage updaten
            $this->db->query(
                "UPDATE tsuchanfrage
                    SET cSeo = ''
                    WHERE kSuchanfrage" . $whereIn
            );
            foreach (Request::verifyGPDataIntegerArray('nAktiv') as $active) {
                $query = $this->db->select('tsuchanfrage', 'kSuchanfrage', $active);
                $this->db->delete(
                    'tseo',
                    ['cKey', 'kKey', 'kSprache'],
                    ['kSuchanfrage', $active, $languageID]
                );
                if ($query === null) {
                    continue;
                }
                // Aktivierte Suchanfragen in tseo eintragen
                $ins           = new stdClass();
                $ins->cSeo     = Seo::checkSeo(Seo::getSeo($query->cSuche));
                $ins->cKey     = 'kSuchanfrage';
                $ins->kKey     = $active;
                $ins->kSprache = $languageID;
                $this->db->insert('tseo', $ins);
                // Aktivierte Suchanfragen in tsuchanfrage updaten
                $upd         = new stdClass();
                $upd->nAktiv = 1;
                $upd->cSeo   = $ins->cSeo;
                $this->db->update('tsuchanfrage', 'kSuchanfrage', $active, $upd);
            }
        }
        $succesMapMessage = '';
        $errorMapMessage  = '';
        foreach ($searchQueries as $searchQuery) {
            $index      = 'mapping_' . $searchQuery->kSuchanfrage;
            $newMapping = Request::pString($index);
            if (
                !isset($_POST[$index])
                || \mb_convert_case($searchQuery->cSuche, \MB_CASE_LOWER) !==
                \mb_convert_case($newMapping, \MB_CASE_LOWER)
            ) {
                if (empty($_POST[$index])) {
                    continue;
                }
                $mapping                 = new stdClass();
                $mapping->kSprache       = $languageID;
                $mapping->cSuche         = $searchQuery->cSuche;
                $mapping->cSucheNeu      = $newMapping;
                $mapping->nAnzahlGesuche = $searchQuery->nAnzahlGesuche;
                $mappedSearch            = $this->db->getSingleObject(
                    'SELECT tsuchanfrage.kSuchanfrage, IF(:mapped = :cSuche, 1, 0) isEqual
                        FROM tsuchanfrage
                        WHERE cSuche = :cSuche',
                    [
                        'cSuche' => $mapping->cSucheNeu,
                        'mapped' => $mapping->cSuche,
                    ]
                );
                if ($mappedSearch !== null && (int)($mappedSearch->isEqual ?? 0) === 0) {
                    $this->db->insert('tsuchanfragemapping', $mapping);
                    $this->db->queryPrepared(
                        'UPDATE tsuchanfrage
                            SET nAnzahlGesuche = nAnzahlGesuche + :cnt
                            WHERE kSprache = :lid
                                AND cSuche = :src',
                        [
                            'cnt' => $searchQuery->nAnzahlGesuche,
                            'lid' => $languageID,
                            'src' => $newMapping
                        ]
                    );
                    $this->db->delete('tsuchanfrage', 'kSuchanfrage', (int)$searchQuery->kSuchanfrage);
                    $this->db->update(
                        'tseo',
                        ['cKey', 'kKey'],
                        ['kSuchanfrage', (int)$searchQuery->kSuchanfrage],
                        (object)['kKey' => (int)$mappedSearch->kSuchanfrage]
                    );

                    $succesMapMessage .= \sprintf(
                        \__('successSearchMap'),
                        $mapping->cSuche,
                        $mapping->cSucheNeu
                    );
                    $succesMapMessage .= '<br />';
                } else {
                    $errorMapMessage .= ((int)($mappedSearch->isEqual ?? 0) === 1
                            ? \sprintf(\__('errorSearchMapLoop'), $mapping->cSuche, $mapping->cSucheNeu)
                            : \__('errorSearchMapToNotExist')
                        ) . '<br />';
                }
            } else {
                $errorMapMessage .= \sprintf(\__('errorSearchMapSelf'), Text::filterXSS($newMapping));
            }
        }
        $this->alertService->addSuccess($succesMapMessage, 'successSearchMap');
        $this->alertService->addError($errorMapMessage, 'errorSearchMap');
        $this->alertService->addSuccess(\__('successSearchRefresh'), 'successSearchRefresh');
    }

    private function assignData(int $languageID): void
    {
        $liveSearchSQL = new SqlObject();
        $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
        if (\mb_strlen(Request::verifyGPDataString('cSuche')) > 0) {
            $query = $this->db->escape(Text::filterXSS(Request::verifyGPDataString('cSuche')));
            if (\mb_strlen($query) > 0) {
                $liveSearchSQL->setWhere(' AND tsuchanfrage.cSuche LIKE :srch');
                $liveSearchSQL->addParam('srch', '%' . $query . '%');
                $this->getSmarty()->assign('cSuche', $query);
            } else {
                $this->alertService->addError(\__('errorSearchTermMissing'), 'errorSearchTermMissing');
            }
        }
        if (Request::verifyGPCDataInt('nSort') > 0) {
            $this->getSmarty()->assign('nSort', Request::verifyGPCDataInt('nSort'));

            switch (Request::verifyGPCDataInt('nSort')) {
                case 1:
                    $liveSearchSQL->setOrder(' tsuchanfrage.cSuche ASC ');
                    break;
                case 11:
                    $liveSearchSQL->setOrder(' tsuchanfrage.cSuche DESC ');
                    break;
                case 2:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche DESC ');
                    break;
                case 22:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAnzahlGesuche ASC ');
                    break;
                case 3:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAktiv DESC ');
                    break;
                case 33:
                    $liveSearchSQL->setOrder(' tsuchanfrage.nAktiv ASC ');
                    break;
            }
        } else {
            $this->getSmarty()->assign('nSort', -1);
        }

        $queryCount        = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfrage
                WHERE kSprache = :lid' . $liveSearchSQL->getWhere(),
            'cnt',
            \array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
        );
        $failedQueryCount  = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfrageerfolglos
                WHERE kSprache = :lid',
            'cnt',
            ['lid' => $languageID]
        );
        $mappingCount      = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tsuchanfragemapping
                WHERE kSprache = :lid',
            'cnt',
            ['lid' => $languageID]
        );
        $paginationQueries = (new Pagination('suchanfragen'))
            ->setItemCount($queryCount)
            ->assemble();
        $paginationFailed  = (new Pagination('erfolglos'))
            ->setItemCount($failedQueryCount)
            ->assemble();
        $paginationMapping = (new Pagination('mapping'))
            ->setItemCount($mappingCount)
            ->assemble();

        $failedQueries  = $this->db->getObjects(
            'SELECT *
                FROM tsuchanfrageerfolglos
                WHERE kSprache = :lid
                ORDER BY nAnzahlGesuche DESC
                LIMIT ' . $paginationFailed->getLimitSQL(),
            ['lid' => $languageID]
        );
        $queryBlacklist = $this->db->getCollection(
            'SELECT *
                FROM tsuchanfrageblacklist
                WHERE kSprache = :lid
                ORDER BY kSuchanfrageBlacklist',
            ['lid' => $languageID]
        )->each(static function (stdClass $item): stdClass {
            $item->cSuche = \htmlentities($item->cSuche);

            return $item;
        })->toArray();
        $queryMapping   = $this->db->getObjects(
            'SELECT *
                FROM tsuchanfragemapping
                WHERE kSprache = :lid
                LIMIT ' . $paginationMapping->getLimitSQL(),
            ['lid' => $languageID]
        );
        $searchQueries  = $this->db->getObjects(
            "SELECT tsuchanfrage.*, tseo.cSeo AS tcSeo
                FROM tsuchanfrage
                LEFT JOIN tseo 
                    ON tseo.cKey = 'kSuchanfrage'
                    AND tseo.kKey = tsuchanfrage.kSuchanfrage
                    AND tseo.kSprache = :lid
                WHERE tsuchanfrage.kSprache = :lid
                    " . $liveSearchSQL->getWhere() . '
                GROUP BY tsuchanfrage.kSuchanfrage
                ORDER BY ' . $liveSearchSQL->getOrder() . '
                LIMIT ' . $paginationQueries->getLimitSQL(),
            \array_merge(['lid' => $languageID], $liveSearchSQL->getParams())
        );
        foreach ($searchQueries as $item) {
            if (isset($item->tcSeo) && \mb_strlen($item->tcSeo) > 0) {
                $item->cSeo = $item->tcSeo;
            }
            unset($item->tcSeo);
        }

        $this->getSmarty()->assign('Suchanfragen', $searchQueries)
            ->assign('Suchanfragenerfolglos', $failedQueries)
            ->assign('Suchanfragenblacklist', $queryBlacklist)
            ->assign('Suchanfragenmapping', $queryMapping)
            ->assign('oPagiSuchanfragen', $paginationQueries)
            ->assign('oPagiErfolglos', $paginationFailed)
            ->assign('oPagiMapping', $paginationMapping);
    }
}
