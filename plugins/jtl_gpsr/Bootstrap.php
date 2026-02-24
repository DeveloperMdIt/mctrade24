<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr;

use Exception;
use JTL\Events\Dispatcher;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\IO\IO;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_gpsr\adminmenu\Controller;
use Plugin\jtl_gpsr\adminmenu\Handler;
use Plugin\jtl_gpsr\frontend\Handler as FrontendHandler;

/**
 * Class Bootstrap
 * @package Plugin\jtl_gpsr
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        $plugin = $this->getPlugin();
        require_once $plugin->getPaths()->getBasePath() . 'vendor/autoload.php';

        if (Shop::isFrontend()) {
            $handler = new FrontendHandler($plugin);
            $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_INC, [$handler, 'smartyInc']);
            $dispatcher->listen('shop.hook.' . \HOOK_ARTIKEL_PAGE, [$handler, 'pageProduct']);
            $dispatcher->listen('shop.hook.' . \HOOK_FILTER_PAGE, [$handler, 'pageProductList']);
        } else {
            $account    = Shop::Container()->getAdminAccount();
            $permission = $account->logged() &&
                ($account->permission('PLUGIN_DETAIL_VIEW_ALL')
                    || $account->permission('PLUGIN_DETAIL_VIEW_' . $plugin->getID())
                );
            if (!$permission) {
                return;
            }

            $handler = new Handler($plugin);
            $dispatcher->listen(
                'shop.hook.' . \HOOK_IO_HANDLE_REQUEST_ADMIN,
                static function ($args) use ($handler) {
                    /** @var IO $io */
                    $io = $args['io'];
                    $io->register('getTemplateBlocks', [$handler, 'getTemplateBlocksAsJson']);
                },
            );
            $dispatcher->listen(
                'shop.hook.' . \HOOK_PLUGIN_SAVE_OPTIONS,
                [$handler, 'generateTemplateFiles']
            );

            $task = Request::postVar('task');
            if (
                !empty($task)
                    && Form::validateToken()
                    && Request::postInt('kPlugin', Request::getInt('kPlugin')) === $plugin->getID()
            ) {
                Controller::getInstance($this->getPlugin())->run($task);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $handler = new Handler($this->getPlugin());
        if ($tabName === 'Info') {
            try {
                $result = $handler->renderAdminMenuInfo($menuID, $smarty);
            } catch (Exception $e) {
                $result = $e->getMessage();
            }

            return $result;
        }

        if ($tabName === 'Darstellung') {
            try {
                $result = $handler->renderAdminMenuPresentation($menuID, $smarty);
            } catch (Exception $e) {
                $result = $e->getMessage();
            }

            return $result;
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }

    /**
     * @inheritDoc
     */
    public function installed(): void
    {
        parent::installed();

        $plugin = $this->getPlugin();
        (Controller::getInstance($plugin, $this->getDB()))->run('resetAllSettingsInstall');
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void
    {
        $plugin = $this->getPlugin();
        (new Handler($plugin))->generateTemplateFiles(['plugin' => $plugin]);
    }
}
