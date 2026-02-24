<?php

namespace Template\admorris_pro\Utils;

use JTL\Shop;

class Favicon
{
    private string $url;
    private string $type;

    public function __construct(private $smarty)
    {
    }

    private function initUrl(): void
    {
        $smarty = $this->smarty;
        $icon = Shop::getSettings([CONF_TEMPLATE])['template']['general']['new_favicon'] ?? null;
        $fullIconPath = PFAD_ROOT . $icon;

        if (!empty($icon) && file_exists($fullIconPath)) {
            $this->url = Shop::getURL() . $icon;
        } elseif (($icon = $smarty->getTemplateVars('shopFaviconURL')) && !empty($icon)) {
            $this->url = $smarty->getTemplateVars('shopFaviconURL');
        } elseif (file_exists(Shop::getURL() . '/favicon.ico')) {
            $this->url = Shop::getURL() . '/favicon.ico';
        } else {
            $this->url = '';
        }
    }

    private function initType(): void
    {
        if (empty($this->url)) {
            return;
        }
        $ext = pathinfo($this->url, PATHINFO_EXTENSION);
        if ($ext === 'svg') {
            $type = 'svg+xml';
        } elseif ($ext === 'ico') {
            $type = 'x-icon';
        } else {
            $type = 'png';
        }
        $type = 'image/' . $type;
        $this->type = $type;
    }

    public function getLinkTag(): string
    {
        $this->initUrl();
        $this->initType();

        if (empty($this->url)) {
            return '';
        }
        return <<<HTML
            <link rel="icon" type="{$this->type}" href="{$this->url}">
        HTML;
    }

}
