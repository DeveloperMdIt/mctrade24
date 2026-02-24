<?php

declare(strict_types=1);

namespace JTL\Widgets;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;

/**
 * Class AbstractWidget
 * @package JTL\Widgets
 */
abstract class AbstractWidget implements WidgetInterface
{
    public JTLSmarty $oSmarty;

    public DbInterface $oDB;

    public ?PluginInterface $oPlugin = null;

    public bool $hasBody = true;

    public string $permission = '';

    /**
     * @inheritdoc
     */
    public function __construct(?JTLSmarty $smarty = null, ?DbInterface $db = null, $plugin = null)
    {
        $this->oSmarty = $smarty ?? Shop::Smarty(false, ContextType::BACKEND);
        $this->oDB     = $db ?? Shop::Container()->getDB();
        $this->oPlugin = $plugin;
        $this->init();
    }

    public function getSmarty(): JTLSmarty
    {
        return $this->oSmarty;
    }

    public function setSmarty(JTLSmarty $oSmarty): void
    {
        $this->oSmarty = $oSmarty;
    }

    public function getDB(): DbInterface
    {
        return $this->oDB;
    }

    public function setDB(DbInterface $oDB): void
    {
        $this->oDB = $oDB;
    }

    public function getPlugin(): PluginInterface
    {
        return $this->oPlugin;
    }

    public function setPlugin(PluginInterface $plugin): void
    {
        $this->oPlugin = $plugin;
    }

    public function init()
    {
    }

    public function getContent()
    {
        return '';
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): void
    {
        $this->permission = $permission;
    }

    public function hasBody(): bool
    {
        return $this->hasBody;
    }

    public function setHasBody(bool $hasBody): void
    {
        $this->hasBody = $hasBody;
    }
}
