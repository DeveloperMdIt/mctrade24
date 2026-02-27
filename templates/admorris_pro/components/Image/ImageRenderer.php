<?php

namespace Template\admorris_pro\components\Image;

use JTL\Shop;
use Plugin\admorris_pro\Utils\Image;
use scc\ComponentInterface;
use scc\ComponentRendererInterface;
use scc\renderers\BlockRenderer;

class ImageRenderer extends BlockRenderer implements ComponentRendererInterface
{
    public function __construct(protected ComponentInterface $component)
    {
    }

    /**
     * @inheritdoc
     */
    public function render(array $params, ...$args): string
    {
        $tpl = $args[0];

        $params = $this->mergeParams($params);

        $Einstellungen = $tpl->getTemplateVars('Einstellungen');
        $admPro = $tpl->getTemplateVars('admPro');
        ;
        $progessiveLoadingActive = isset($Einstellungen["template"]["general"]["progressive_loading"]) && $Einstellungen["template"]["general"]["progressive_loading"] === "Y";

        $useWebP = $admPro->webpBrowserSupport() && $params['webp']->getValue() === true && \JTL\Media\Image::hasWebPSupport();

        if ($useWebP) {
            /* Some paths might not have webp versions (e.g. external plugins) */
            $excludePaths = [
                'OPC/Portlets',
                'plugins',
            ];
            foreach ($excludePaths as $excludePath) {
                if (strpos($params['src']->getValue(), $excludePath) !== false) {
                    $useWebP = false;
                    break;
                }
            }
        }

        if ($useWebP) {
            /* If the image renderer is called with a data-desc attribute, we should not use webp */
            $data = $params['data']->getValue();
            if (isset($data) && is_array($data) && isset($data['desc'])) {
                $useWebP = false;
            }
        }

        $tpl->assign('useWebP', $useWebP);

        if ($useWebP) {
            if ($params['srcset']->hasValue()) {
                $params['srcset']->setValue(preg_replace("/\.(?i)(jpg|jpeg|png)/", ".webp", $params['srcset']->getValue()));
            }
            if ($params['src']->hasValue()) {
                if ($params['opc']->getValue() === false) {
                    $params['src']->setValue(preg_replace("/\.(?i)(jpg|jpeg|png)/", ".webp", $params['src']->getValue()));
                }
            }
        }

        $useProgressiveLoading = false;
        $usePlaceholder = false;

        // helper vars if opc image
        $opcSrcSet = null;
        $opcSrcSetString = null;
        $lazy = $params['lazy']->getValue() === true;

        // handle opc image
        if ($params['opc']->getValue() === true) {
            $scaling = 0;
            if ($params['scaling']->getValue() !== 0) {
                $scaling = $params['scaling']->getValue();
            }

            try {
                // get opc srcSets
                $opcSrcSet = Image::getOPCImageSrcSet($params['src']->getValue());
                $opcSrcSet = ($useWebP) ? $opcSrcSet->webp : $opcSrcSet->default;
                $opcSrcSetString = Image::getOPCImageSrcSetString($opcSrcSet, $scaling);
            } catch (\Throwable $th) {
                if (Shop::isAdmin()) {
                    return 'Image Error: ' . $th->getMessage();
                }
                return '';
            }

            // use largest src set image if no explicit width / height
            $biggestImage = end($opcSrcSet);
            if ($params['height']->hasValue() === false) {
                $params['height']->setValue($biggestImage->height);
            }
            if ($params['width']->hasValue() === false) {
                $params['width']->setValue($biggestImage->width);
            }

            $params['src']->setValue($biggestImage->path);

            if ($progessiveLoadingActive) {
                if (!$params['progressiveLoading']->hasValue()) {
                    $params['progressiveLoading']->setValue($opcSrcSet[0]->path);
                }
                if (!$lazy) {
                    $params['progressivePlaceholder']->setValue(true);
                }
            }
        }

        // in templates fetched via ajax like the basket dropdown $Einstellungen doesn't exist
        if (!empty($Einstellungen["template"]["general"])) {
            $useProgressiveLoading = !empty($params['progressiveLoading']->getValue()) && !$params['progressivePlaceholder']->getValue() && $progessiveLoadingActive && strpos($params['src']->getValue(), 'keinBild.gif') === false;
            $usePlaceholder = $progessiveLoadingActive && $params['progressivePlaceholder']->getValue();
        }

        if ($useProgressiveLoading && $params['progressiveLoading']->hasValue() && $useWebP) {
            $params['progressiveLoading']->setValue(preg_replace("/\.(?i)(jpg|jpeg|png)/", ".webp", $params['progressiveLoading']->getValue()));
        }

        $rounded = '';

        if ($params['rounded']->getValue() !== false) {
            if ($params['rounded']->getValue() === true) {
                $rounded = 'rounded';
            } else {
                $rounded = 'img-' . $params['rounded']->getValue();
            }
        }

        // get dimensions
        if ($params['height']->hasValue()) {
            $height = $params['height']->getValue();
        }
        if ($params['width']->hasValue()) {
            $width = $params['width']->getValue();
        }

        if (empty($height)) {
            // First check the sourceset images, because it can be that the src fallback image isn't generated yet
            if ($params['srcset']->hasValue()) {
                $size = Image::imageSizeFromSrcset($params['srcset']->getValue());
            } else {
                $size = Image::imageSize($params['src']->getValue());
            }
            if (!empty($size) && is_numeric($size->width) && is_numeric($size->height)) {
                $width = floor($size->width);
                $height = floor($size->height);
            } else {
                $width = 'auto';
                $height = 'auto';
            }
        }

        $tpl->assign('opcSrcSet', $opcSrcSet)
            ->assign('opcSrcSetString', $opcSrcSetString)
            ->assign('width', $width)
            ->assign('height', $height)
            ->assign('rounded', $rounded)
            ->assign('lazy', $lazy)
            ->assign('useProgressiveLoading', $useProgressiveLoading)
            ->assign('usePlaceholder', $usePlaceholder);

        $oldParams = $tpl->getTemplateVars('params');
        $html = $tpl->assign('params', $params)
            ->assign('parentSmarty', $tpl->smarty)
            ->fetch($this->component->getTemplate());
        if ($oldParams !== null) {
            $tpl->assign('params', $oldParams);
        }

        return $html;
    }
}
