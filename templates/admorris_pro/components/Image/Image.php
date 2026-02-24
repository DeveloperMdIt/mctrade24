<?php declare(strict_types=1);


namespace Template\admorris_pro\components\Image;

use scc\ComponentProperty;
use scc\ComponentPropertyType;
use JTL\Shop;
use scc\ComponentInterface;
use Template\admorris_pro\components\Image\ImageRenderer;

/**
 * Class Image
 * @package scc\components
 */
class Image extends \scc\components\AbstractFunctionComponent implements ComponentInterface
{
    /**
     * Image constructor.
     */
    public function __construct(string $name = 'responsiveImage')
    {
        parent::__construct();
        
        $this->setTemplate(dirname(__FILE__) . '/image.tpl');
        $this->setName($name);


        $argsArray = [
            ['src'],
            ['srcset'],
            ['progressiveLoading'],
            ['sizes'],
            ['title'],
            ['alt', ''],
            ['width', null, ComponentPropertyType::TYPE_NUMERIC],
            ['height', null, ComponentPropertyType::TYPE_NUMERIC],
            ['block', false, ComponentPropertyType::TYPE_BOOL],
            ['fluid', true, ComponentPropertyType::TYPE_BOOL],
            ['fluid-grow', false, ComponentPropertyType::TYPE_BOOL],
            ['lazy', false, ComponentPropertyType::TYPE_BOOL],
            ['nativeLazyLoading', null, ComponentPropertyType::TYPE_BOOL],
            ['fetchpriority', null],
            ['webp', true, ComponentPropertyType::TYPE_BOOL],
            ['rounded', false, ComponentPropertyType::TYPE_BOOL],
            ['thumbnail', false, ComponentPropertyType::TYPE_BOOL],
            ['left', false, ComponentPropertyType::TYPE_BOOL],
            ['right', false, ComponentPropertyType::TYPE_BOOL],
            ['center', false, ComponentPropertyType::TYPE_BOOL],
            ['blank', false, ComponentPropertyType::TYPE_BOOL],
            ['progressivePlaceholder', false, ComponentPropertyType::TYPE_BOOL],
            ['opc', $name === 'responsiveImage', ComponentPropertyType::TYPE_BOOL],
            ['scaling', 0, ComponentPropertyType::TYPE_NUMERIC],
        ];

        foreach ($argsArray as $args) {
            $this->addParam(new ComponentProperty(...$args));
        }

        $this->setRenderer(new ImageRenderer($this));

    }
}
