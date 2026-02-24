<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Plugin\PluginInterface;

trait AttributesHelper
{
    /**
     * Transform attribute name to fit clerk requirements.
     *
     * @param string $attr
     * @return string
     */
    protected function transformAttributeName(string $attr): string
    {
        $search = ["Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´", "Æ", "æ", "Ø", "ø", "Å", "å"];
        $replace = ["Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "", "Ae", "ae", "Oe", "oe", "Aa", "aa"];
        $attr = str_replace($search, $replace, $attr);

        return strtolower(preg_replace('/\s+|-+/', '_', $attr));
    }

    protected function getFacetAttributes(PluginInterface $plugin): array
    {
        $attrs = $plugin->getConfig()->getValue(Config::SETTING_FACETS_ATTRIBUTES);
        if (empty($attrs)) {
            return [];
        }

        return explode(',', str_replace(' ', '', $attrs));
    }

    protected function getFacetMultiAttributes(PluginInterface $plugin): array
    {
        $attrs = $plugin->getConfig()->getValue(Config::SETTING_FACETS_MULTI_ATTRIBUTES);
        if (empty($attrs)) {
            return [];
        }

        return explode(',', str_replace(' ', '', $attrs));
    }
}
