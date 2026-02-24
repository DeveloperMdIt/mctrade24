<?php

declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class News
 * @package JTL\Widgets
 */
class News extends AbstractWidget
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->setPermission('DASHBOARD_ALL');
    }

    /**
     * @inheritdoc
     */
    public function getContent(): string
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/news.tpl');
    }
}
