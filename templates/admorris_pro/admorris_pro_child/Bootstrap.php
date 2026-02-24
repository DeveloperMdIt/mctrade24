<?php
namespace Template\admorris_pro_child;

use JTL\Shop;

/**
 * Class Bootstrap
 * @package Template\NOVAChild
 */
class Bootstrap extends \Template\admorris_pro\Bootstrap
{
    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        parent::boot();
        // whatever you do, always call parent::boot() or delete this method!
    }

    protected function registerPlugins(): void
    {
        parent::registerPlugins();
        // whatever you do, always call parent::registerPlugins() or delete this method!
        Shop::Smarty()->registerClass('Func', Functions::class);
    }
}
