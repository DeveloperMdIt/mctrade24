<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;

abstract class Controller
{
    protected const TEMPLATE_TYPE_FRONTEND = 'FRONTEND';
    protected const TEMPLATE_TYPE_BACKEND = 'BACKEND';

    public function __construct(
        protected PluginInterface $plugin,
        protected JTLSmarty $smarty,
        protected AlertServiceInterface $alertService
    ) {
    }

    /**
     * Get the a template file
     * @param string $template
     * @param string $type
     * @return string
     */
    protected function getTemplate(string $template, string $type = self::TEMPLATE_TYPE_FRONTEND): string
    {
        $path = $type === self::TEMPLATE_TYPE_BACKEND
            ? $this->plugin->getPaths()->getAdminPath()
            : $this->plugin->getPaths()->getFrontendPath();

        $customTemplatePath = $path . $template . '_custom.tpl';
        $templatePath = $path . $template . '.tpl';

        if (file_exists($customTemplatePath)) {
            return $customTemplatePath;
        }

        return $templatePath;
    }

    /**
     * Render a template file
     * @param string $template Name of the template
     * @param string $method PQ Method (append, prepend, after, before, replaceWith)
     * @param string $selector PQ Selector
     * @return void
     */
    protected function render(string $template, string $method, string $selector): void
    {
        if (trim($selector) !== '') {
            pq($selector)->{$method}($this->smarty->fetch($this->getTemplate($template)));
        }
    }
}
