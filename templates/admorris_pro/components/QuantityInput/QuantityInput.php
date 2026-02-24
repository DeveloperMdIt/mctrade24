<?php

namespace Template\admorris_pro\components\QuantityInput;

use scc\ComponentProperty;
use scc\ComponentPropertyType as Type;
use JTL\Shop;
use scc\ComponentInterface;
use scc\components\AbstractFunctionComponent;

class QuantityInput extends AbstractFunctionComponent implements ComponentInterface
{
  public function __construct()
  {
    parent::__construct();
        
    $this->setTemplate(dirname(__FILE__) . '/quantity_input.tpl');
    $this->setName('quantityInput');

    $argsArray = [
        ['article', null, Type::TYPE_OBJECT],
        ['name'],
        ['wrapperClass', ''],
        ['buttonClass', ''],
        ['idPrefix', ''],
        ['min', null, Type::TYPE_NUMERIC],
        ['max', null, Type::TYPE_NUMERIC],
        ['value', null, Type::TYPE_NUMERIC],
        ['step', null],
        ['disabled', false, Type::TYPE_BOOL],
    ];

    foreach ($argsArray as $args) {
        $this->addParam(new ComponentProperty(...$args));
    }
  }
}