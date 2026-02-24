<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Pagination\Pagination;
use JTL\Slide;
use JTL\Slider;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * Class SliderController
 * @package JTL\Router\Controller\Backend
 */
class SliderController extends AbstractBackendController
{
    private string $action;

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SLIDER_VIEW);
        $this->getText->loadAdminLocale('pages/slider');

        $this->action = isset($_REQUEST['action']) && Form::validateToken()
            ? $_REQUEST['action']
            : 'view';
        $sliderID     = Request::postInt('kSlider', (int)($_REQUEST['id'] ?? 0));
        if ($this->action === 'slide_set') {
            $this->actionSlideSet($sliderID);
        } else {
            $smarty->assign('disabled', '');
            if ($this->action !== 'view' && !empty($_POST) && Form::validateToken()) {
                $response = $this->actionView($sliderID);
                if ($response !== null) {
                    return $response;
                }
            }
        }
        $result = match ($this->action) {
            'slides' => $this->actionSlides($sliderID),
            'edit'   => $this->actionEdit($sliderID),
            'new'    => $this->actionNew(),
            'delete' => $this->actionDelete($sliderID),
            default  => null,
        };
        if ($result instanceof ResponseInterface) {
            return $result;
        }
        $this->assignPagination();

        return $smarty->assign('action', $this->action)
            ->assign('kSlider', $sliderID)
            ->assign('validPageTypes', BoxController::getMappedValidPageTypes())
            ->assign('route', $this->route)
            ->getResponse('slider.tpl');
    }

    private function getExtension(int $sliderID): ?stdClass
    {
        $data = $this->db->select('textensionpoint', 'cClass', 'slider', 'kInitial', $sliderID);
        if ($data !== null) {
            $data->kExtensionPoint = (int)$data->kExtensionPoint;
            $data->kSprache        = (int)$data->kSprache;
            $data->kKundengruppe   = (int)$data->kKundengruppe;
            $data->nSeite          = (int)$data->nSeite;
            $data->kInitial        = (int)$data->kInitial;
        }

        return $data;
    }

    private function actionSlideSet(int $sliderID): void
    {
        $filtered = Text::filterXSS($_REQUEST);
        foreach (\array_keys((array)$filtered['aSlide']) as $item) {
            $slide = new Slide(0, $this->db);
            /** @var array<string, mixed> $aSlide */
            $aSlide = $filtered['aSlide'][$item];
            if (!\str_contains((string)$item, 'neu')) {
                $slide->setID((int)$item);
            }
            $slide->setSliderID($sliderID);
            $slide->setTitle(\htmlspecialchars($aSlide['cTitel'], \ENT_COMPAT | \ENT_HTML401, \JTL_CHARSET));
            $slide->setImage($aSlide['cBild']);
            $slide->setThumbnail($aSlide['cThumbnail']);
            $slide->setText($aSlide['cText']);
            $slide->setLink($aSlide['cLink']);
            $slide->setSort((int)$aSlide['nSort']);
            if ((int)$aSlide['delete'] === 1) {
                $slide->delete();
            } else {
                $slide->save();
            }
        }
        $this->cache->flushTags([\CACHING_GROUP_CORE]);
    }

    private function actionView(int $sliderID): ?ResponseInterface
    {
        $filtered = Text::filterXSS($_POST);
        $slider   = new Slider($this->db);
        $slider->load($sliderID, false);
        $slider->set((object)$filtered);
        // extensionpoint
        $languageID      = Request::pInt('kSprache');
        $customerGroupID = Request::pInt('kKundengruppe');
        $pageType        = Request::pInt('nSeitenTyp');
        /** @var string $cKey */
        $cKey      = Request::postVar('cKey');
        $cKeyValue = '';
        $cValue    = '';
        if ($pageType === \PAGE_ARTIKEL) {
            $cKey      = 'kArtikel';
            $cKeyValue = 'article_key';
            $cValue    = $filtered[$cKeyValue];
        } elseif ($pageType === \PAGE_ARTIKELLISTE) {
            $filter    = [
                'kMerkmalWert' => 'attribute_key',
                'kKategorie'   => 'categories_key',
                'kHersteller'  => 'manufacturer_key',
                'cSuche'       => 'keycSuche'
            ];
            $cKeyValue = $filter[$cKey];
            $cValue    = $filtered[$cKeyValue];
        } elseif ($pageType === \PAGE_EIGENE) {
            $cKey      = 'kLink';
            $cKeyValue = 'link_key';
            $cValue    = $filtered[$cKeyValue];
        }
        if (!empty($cKeyValue) && empty($cValue)) {
            $this->alertService->addError(\sprintf(\__('errorKeyMissing'), $cKey), 'errorKeyMissing');

            return null;
        }
        if (empty($slider->getEffects())) {
            $slider->setEffects('random');
        }
        if ($slider->save() === true) {
            $this->db->delete(
                'textensionpoint',
                ['cClass', 'kInitial'],
                ['slider', $slider->getID()]
            );
            $extension                = new stdClass();
            $extension->kSprache      = $languageID;
            $extension->kKundengruppe = $customerGroupID;
            $extension->nSeite        = $pageType;
            $extension->cKey          = $cKey;
            $extension->cValue        = $cValue;
            $extension->cClass        = 'slider';
            $extension->kInitial      = $slider->getID();
            $this->db->insert('textensionpoint', $extension);
            $this->alertService->addSuccess(
                \__('successSliderSave'),
                'successSliderSave',
                ['saveInSession' => true]
            );
            $this->cache->flushTags([\CACHING_GROUP_CORE]);

            return new RedirectResponse($this->baseURL . $this->route);
        }
        $this->alertService->addError(\__('errorSliderSave'), 'errorSliderSave');

        return null;
    }

    private function actionEdit(int $sliderID): bool
    {
        $slider = new Slider($this->db);
        $loaded = $slider->load($sliderID, false);
        $smarty = $this->getSmarty();
        $smarty->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oExtension', $this->getExtension($sliderID));

        if ($slider->getEffects() !== 'random') {
            $effects = \explode(';', $slider->getEffects());
            $options = '';
            foreach ($effects as $cValue) {
                $options .= '<option value="' . $cValue . '">' . $cValue . '</option>';
            }
            $smarty->assign('cEffects', $options);
        } else {
            $smarty->assign('checked', 'checked="checked"')
                ->assign('disabled', 'disabled="true"');
        }
        $smarty->assign('oSlider', $slider);

        if ($loaded) {
            return true;
        }
        $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
        $this->action = 'view';

        return false;
    }

    private function actionSlides(int $sliderID): bool
    {
        $slider = new Slider($this->db);
        $loaded = $slider->load($sliderID, false);
        $this->getSmarty()->assign('oSlider', $slider);
        if ($loaded) {
            return true;
        }
        $this->alertService->addError(\__('errorSliderNotFound'), 'errorSliderNotFound');
        $this->action = 'view';

        return false;
    }

    private function actionNew(): bool
    {
        $this->getSmarty()->assign('checked', 'checked="checked"')
            ->assign('customerGroups', CustomerGroup::getGroups())
            ->assign('oSlider', new Slider($this->db));

        return true;
    }

    private function actionDelete(int $sliderID): false|RedirectResponse
    {
        $slider = new Slider($this->db);
        $slider->load($sliderID, false);
        if ($slider->delete() === true) {
            $this->cache->flushTags([\CACHING_GROUP_CORE]);

            return new RedirectResponse($this->baseURL . $this->route);
        }
        $this->alertService->addError(\__('errorSliderRemove'), 'errorSliderRemove');

        return false;
    }

    private function assignPagination(): void
    {
        $pagination = (new Pagination('sliders'))
            ->setRange(4)
            ->setItemArray($this->db->getObjects('SELECT * FROM tslider'))
            ->assemble();

        $this->getSmarty()->assign('pagination', $pagination)
            ->assign('oSlider_arr', $pagination->getPageItems());
    }
}
