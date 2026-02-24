<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\adminmenu;

use JTL\Helpers\Request;

/**
 * Class GPSRTemplateblock
 * @package Plugin\jtl_gpsr\adminmenu
 */
class GPSRTemplateblock extends GPSRTemplatefile
{
    protected static function getRealContext($context): string
    {
        return 'block:' . $context;
    }

    /**
     * @inheritDoc
     */
    protected function getEmpyOption(): array
    {
        $option = (object)[
            'cWert' => '$path',
            'cName' => __('Templatedatei existiert nicht'),
            'nSort' => 1,
        ];

        return [$option];
    }

    /**
     * @inheritDoc
     */
    protected function getTemplateList(string $tplDir, string $context): array
    {
        $blockList = [];
        $varName   = 'gpsr_templatefile_' . $this->context;
        $tplFile   = (string)Request::postVar($varName, $this->config->getValue($varName));
        $tplFile   = str_replace('../', '', $tplFile);

        if (!\str_starts_with($tplFile, $context) || !\file_exists($tplDir . $tplFile)) {
            return [];
        }

        $content = \file_get_contents($tplDir . $tplFile);
        if (
            is_string($content)
            && preg_match_all('/\{block name=[\'\"]([a-zA-Z\-_]+)[\'\"]}/', $content, $matches, PREG_PATTERN_ORDER)
        ) {
            $blockList = $matches[1];
        }

        return $blockList;
    }
}
