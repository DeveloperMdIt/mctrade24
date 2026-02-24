<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Onboarding;

use JTL\Link\LinkInterface;
use JTL\Plugin\PluginInterface;

/**
 * Class Onboarding
 * @package Plugin\jtl_paypal_commerce\Onboarding
 */
class Onboarding
{
    /**
     * @param PluginInterface $plugin
     * @return string|null
     */
    public static function getOnboardingFetchURL(PluginInterface $plugin): ?string
    {
        /** @var LinkInterface $link */
        $link = $plugin->getLinks()->getLinks()->first(static function (LinkInterface $link) {
            return $link->getTemplate() === 'onboarding.tpl';
        });
        if ($link === null || $link->getSEO() === \ltrim($_SERVER['REQUEST_URI'], '/')) {
            return null;
        }

        return $link->getURL();
    }
}
