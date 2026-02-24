<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Media\Image;
use JTL\Media\IMedia;
use JTL\Media\Media;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ImagesController
 * @package JTL\Router\Controller\Backend
 */
class ImagesController extends AbstractBackendController
{
    /**
     * @inheritdoc
     */
    public function getResponse(
        ServerRequestInterface $request,
        array $args,
        JTLSmarty $smarty
    ): ResponseInterface {
        $this->getText->loadAdminLocale('pages/bilder');
        $this->smarty = $smarty;
        $this->checkPermissions(Permissions::SETTINGS_IMAGES_VIEW);
        if (isset($_POST['speichern']) && Form::validateToken()) {
            $this->actionSaveConfig();
        }

        $indices = [
            'kategorien'    => \__('categories'),
            'variationen'   => \__('variations'),
            'artikel'       => \__('product'),
            'hersteller'    => \__('manufacturer'),
            'merkmal'       => \__('attributes'),
            'merkmalwert'   => \__('attributeValues'),
            'opc'           => 'OPC',
            'konfiggruppe'  => \__('configGroups'),
            'news'          => \__('news'),
            'newskategorie' => \__('newscategory')
        ];
        $this->getAdminSectionSettings(\CONF_BILDER);

        return $smarty->assign('indices', $indices)
            ->assign('imgConf', Shop::getSettingSection(\CONF_BILDER))
            ->assign('sizes', ['mini', 'klein', 'normal', 'gross'])
            ->assign('dims', ['breite', 'hoehe'])
            ->assign('route', $this->route)
            ->getResponse('bilder.tpl');
    }

    private function actionSaveConfig(): void
    {
        $shopSettings = Shopsetting::getInstance($this->db, $this->cache);
        $oldConfig    = $shopSettings->getSettings([\CONF_BILDER])['bilder'];
        $this->saveAdminSectionSettings(
            \CONF_BILDER,
            Text::filterXSS($_POST),
            [\CACHING_GROUP_OPTION, \CACHING_GROUP_ARTICLE, \CACHING_GROUP_CATEGORY]
        );
        $shopSettings->reset();
        $newConfig     = $shopSettings->getSettings([\CONF_BILDER])['bilder'];
        $confDiff      = \array_diff_assoc($oldConfig, $newConfig);
        $cachesToClear = [];
        $media         = Media::getInstance();
        foreach (\array_keys($confDiff) as $item) {
            if (
                \str_contains($item, 'quali')
                || \str_contains($item, 'container')
                || \str_contains($item, 'skalieren')
                || \str_contains($item, 'hintergrundfarbe')
            ) {
                $cachesToClear = $media->getRegisteredClassNames();
                break;
            }
            $cachesToClear[] = match (true) {
                \str_contains($item, 'hersteller')   => Media::getClass(Image::TYPE_MANUFACTURER),
                \str_contains($item, 'variation')    => Media::getClass(Image::TYPE_VARIATION),
                \str_contains($item, 'kategorie')    => Media::getClass(Image::TYPE_CATEGORY),
                \str_contains($item, 'merkmalwert')  => Media::getClass(Image::TYPE_CHARACTERISTIC_VALUE),
                \str_contains($item, 'merkmal_')     => Media::getClass(Image::TYPE_CHARACTERISTIC),
                \str_contains($item, 'opc')          => Media::getClass(Image::TYPE_OPC),
                \str_contains($item, 'konfiggruppe') => Media::getClass(Image::TYPE_CONFIGGROUP),
                \str_contains($item, 'artikel')      => Media::getClass(Image::TYPE_PRODUCT),
                default                              => null
            };
            if (\str_contains($item, 'news')) {
                $cachesToClear[] = Media::getClass(Image::TYPE_NEWS);
                $cachesToClear[] = Media::getClass(Image::TYPE_NEWSCATEGORY);
            }
        }
        foreach (\array_filter(\array_unique($cachesToClear)) as $class) {
            /** @var IMedia $class */
            $class::clearCache();
        }
    }
}
