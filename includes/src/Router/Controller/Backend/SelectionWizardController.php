<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Extensions\SelectionWizard\Group;
use JTL\Extensions\SelectionWizard\Question;
use JTL\Extensions\SelectionWizard\Wizard;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SelectionWizardController
 * @package JTL\Router\Controller\Backend
 */
class SelectionWizardController extends AbstractBackendController
{
    private string $tab = 'uebersicht';

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::EXTENSION_SELECTIONWIZARD_VIEW);
        $nice = Nice::getInstance($this->db, $this->cache);
        $this->getText->loadAdminLocale('pages/auswahlassistent');
        $this->getText->loadConfigLocales();
        $this->setLanguage();
        $this->step = 'uebersicht';
        if (!$nice->checkErweiterung(\SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
            return $this->handleLicenseFail();
        }
        if (Request::verifyGPDataString('tab') !== '') {
            $this->tab = Request::verifyGPDataString('tab');
        }
        $this->handlePostData();
        $this->getAdminSectionSettings(\CONF_AUSWAHLASSISTENT);

        return $this->renderTemplate();
    }

    private function handleLicenseFail(): ResponseInterface
    {
        $this->getSmarty()->assign('noModule', true);

        return $this->renderTemplate();
    }

    private function renderTemplate(): ResponseInterface
    {
        return $this->getSmarty()->assign('languageID', $this->currentLanguageID)
            ->assign('route', $this->route)
            ->assign('step', $this->step)
            ->assign('cTab', $this->tab)
            ->getResponse('auswahlassistent.tpl');
    }

    /**
     * @param array<string, mixed> $postData
     */
    public function handleNew(array $postData, Question $question): void
    {
        if ($postData['a'] === 'newGrp') {
            $this->step = 'edit-group';
        } elseif ($postData['a'] === 'newQuest') {
            $this->step = 'edit-question';
        } elseif ($postData['a'] === 'addQuest') {
            $question->cFrage                  = \htmlspecialchars(
                $postData['cFrage'],
                \ENT_COMPAT | \ENT_HTML401,
                \JTL_CHARSET
            );
            $question->kMerkmal                = Request::pInt('kMerkmal');
            $question->kAuswahlAssistentGruppe = Request::pInt('kAuswahlAssistentGruppe');
            $question->nSort                   = Request::pInt('nSort');
            $question->nAktiv                  = Request::pInt('nAktiv');

            if (Request::pInt('kAuswahlAssistentFrage') > 0) {
                $question->kAuswahlAssistentFrage = Request::pInt('kAuswahlAssistentFrage');
                $checks                           = $question->updateQuestion();
            } else {
                $checks = $question->saveQuestion();
            }
            if ((!\is_array($checks) && $checks) || (\is_array($checks) && \count($checks) === 0)) {
                $this->cache->flushTags([\CACHING_GROUP_CORE]);
                $this->alertService->addSuccess(\__('successQuestionSaved'), 'successQuestionSaved');
                $this->tab = 'uebersicht';
            } elseif (\is_array($checks) && \count($checks) > 0) {
                $this->step = 'edit-question';
                $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
                $this->getSmarty()->assign('cPost_arr', $postData)
                    ->assign('cPlausi_arr', $checks)
                    ->assign('kAuswahlAssistentFrage', (int)($postData['kAuswahlAssistentFrage'] ?? 0));
            }
        }
    }

    /**
     * @param array<string, mixed> $postData
     */
    public function addGroup(array $postData, Group $group): void
    {
        $group->kSprache      = $this->currentLanguageID;
        $group->cName         = \htmlspecialchars(
            $postData['cName'],
            \ENT_COMPAT | \ENT_HTML401,
            \JTL_CHARSET
        );
        $group->cBeschreibung = $postData['cBeschreibung'];
        $group->nAktiv        = Request::pInt('nAktiv');

        if (Request::pInt('kAuswahlAssistentGruppe') > 0) {
            $group->kAuswahlAssistentGruppe = Request::pInt('kAuswahlAssistentGruppe');
            $checks                         = $group->updateGroup($postData);
        } else {
            $checks = $group->saveGroup($postData);
        }
        if ((!\is_array($checks) && $checks) || (\is_array($checks) && \count($checks) === 0)) {
            $this->step = 'uebersicht';
            $this->tab  = 'uebersicht';
            $this->cache->flushTags([\CACHING_GROUP_CORE]);
            $this->alertService->addSuccess(\__('successGroupSaved'), 'successGroupSaved');
        } elseif (\is_array($checks) && \count($checks) > 0) {
            $this->step = 'edit-group';
            $this->alertService->addError(\__('errorFillRequired'), 'errorFillRequired');
            $this->getSmarty()->assign('cPost_arr', $postData)
                ->assign('cPlausi_arr', $checks)
                ->assign('kAuswahlAssistentGruppe', Request::pInt('kAuswahlAssistentGruppe'));
        }
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function deleteGroup(array $postData, Group $group): void
    {
        if ($group->deleteGroup($postData['kAuswahlAssistentGruppe_arr'] ?? [])) {
            $this->cache->flushTags([\CACHING_GROUP_CORE]);
            $this->alertService->addSuccess(\__('successGroupDeleted'), 'successGroupDeleted');
        } else {
            $this->alertService->addError(\__('errorGroupDeleted'), 'errorGroupDeleted');
        }
    }

    private function editQuestion(Group $group): void
    {
        $defaultLanguage = $this->db->select('tsprache', 'cShopStandard', 'Y');
        $select          = 'tmerkmal.*';
        $join            = '';
        if ($defaultLanguage !== null && (int)$defaultLanguage->kSprache !== $this->currentLanguageID) {
            $select = 'tmerkmalsprache.*';
            $join   = ' JOIN tmerkmalsprache ON tmerkmalsprache.kMerkmal = tmerkmal.kMerkmal
                        AND tmerkmalsprache.kSprache = ' . $this->currentLanguageID;
        }
        $attributes = $this->db->getObjects(
            'SELECT ' . $select . '
                FROM tmerkmal
                ' . $join . '
                ORDER BY tmerkmal.nSort'
        );
        $this->getSmarty()->assign('oMerkmal_arr', $attributes)
            ->assign(
                'oAuswahlAssistentGruppe_arr',
                $group->getGroups($this->currentLanguageID, false, false, true)
            );
    }

    private function deleteQuestion(Question $question): void
    {
        if ($question->deleteQuestion([Request::gInt('q')])) {
            $this->alertService->addSuccess(\__('successQuestionDeleted'), 'successQuestionDeleted');
            $this->cache->flushTags([\CACHING_GROUP_CORE]);
        } else {
            $this->alertService->addError(\__('errorQuestionDeleted'), 'errorQuestionDeleted');
        }
    }

    public function handlePostData(): void
    {
        $postData = Text::filterXSS($_POST);
        $group    = new Group();
        $question = new Question();
        $csrfOK   = Form::validateToken();
        if (isset($postData['a']) && $csrfOK) {
            $this->handleNew($postData, $question);
        } elseif ($csrfOK && Request::getVar('a') === 'delQuest' && Request::gInt('q') > 0) {
            $this->deleteQuestion($question);
        } elseif ($csrfOK && Request::getVar('a') === 'editQuest' && Request::gInt('q') > 0) {
            $this->step = 'edit-question';
            $this->getSmarty()->assign('oFrage', new Question(Request::gInt('q'), false));
        }

        if (isset($postData['a']) && $csrfOK) {
            if ($postData['a'] === 'addGrp') {
                $this->addGroup($postData, $group);
            } elseif ($postData['a'] === 'delGrp') {
                $this->deleteGroup($postData, $group);
            } elseif ($postData['a'] === 'saveSettings') {
                $this->step = 'uebersicht';
                $this->saveAdminSectionSettings(\CONF_AUSWAHLASSISTENT, $postData);
                $this->cache->flushTags([\CACHING_GROUP_CORE]);
            }
        } elseif ($csrfOK && Request::getVar('a') === 'editGrp' && Request::gInt('g') > 0) {
            $this->step = 'edit-group';
            $this->getSmarty()->assign('oGruppe', new Group(Request::gInt('g'), false, false, true));
        }
        if ($this->step === 'uebersicht') {
            $this->getSmarty()->assign(
                'oAuswahlAssistentGruppe_arr',
                $group->getGroups($this->currentLanguageID, false, false, true)
            );
        } elseif ($this->step === 'edit-group') {
            $this->getSmarty()->assign('oLink_arr', Wizard::getLinks());
        } elseif ($this->step === 'edit-question') {
            $this->editQuestion($group);
        }
    }
}
