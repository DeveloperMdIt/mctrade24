<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping;

use JTL\Plugin\Bootstrapper;
use JTL\Router\Route;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_google_shopping\Backend\CustomAttributes;
use Plugin\jtl_google_shopping\Backend\CustomExports;
use Plugin\jtl_google_shopping\Backend\CustomMapping;
use Plugin\jtl_google_shopping\Backend\Installer;

/**
 * Class Bootstrap
 * @package Plugin\jtl_google_shopping
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritDoc
     */
    public function installed(): void
    {
        parent::installed();
        $installer = new Installer($this->getPlugin(), $this->getDB());
        $installer->install();
    }

    /**
     * @inheritDoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
        if ($deleteData) {
            $installer = new Installer($this->getPlugin(), $this->getDB());
            $installer->uninstall();
        }
        parent::uninstalled($deleteData);
    }

    /**
     * @inheritDoc
     */
    public function preUpdate($oldVersion, $newVersion): void
    {
        parent::preUpdate($oldVersion, $newVersion);
        $this->getDB()->queryPrepared(
            'UPDATE texportformat
                SET kPlugin = 99999999
                WHERE kPlugin = :pid
                    AND cName NOT IN (:name1, :name2)',
            [
                'name1' => 'Google Shopping (Plugin)',
                'name2' => 'Google Review (Plugin)',
                'pid'   => $this->getPlugin()->getID()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function updated($oldVersion, $newVersion): void
    {
        parent::updated($oldVersion, $newVersion);
        $this->getDB()->queryPrepared(
            'UPDATE texportformat
                SET kPlugin = :pid
                WHERE kPlugin = 99999999',
            ['pid' => $this->getPlugin()->getID()]
        );
    }

    /**
     * @inheritDoc
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $smarty->assign('kPluginAdminMenu', $menuID)
            ->assign('exportPath', Route::EXPORT);
        if ($tabName === 'Export Attributes') {
            return CustomAttributes::handleRequest($this->getPlugin(), $this->getDB(), $_POST)->display($smarty);
        }
        if ($tabName === 'Feature Mapping') {
            return CustomMapping::handleRequest($this->getPlugin(), $this->getDB(), $_POST)->display($smarty);
        }
        if ($tabName === 'Additional Exports') {
            return CustomExports::handleRequest($this->getPlugin(), $this->getDB(), $_POST)->display($smarty);
        }

        return parent::renderAdminMenuTab($tabName, $menuID, $smarty);
    }
}
