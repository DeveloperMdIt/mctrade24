<?php

declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Help
 * @package JTL\Widgets
 */
class Help extends AbstractWidget
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
        return $this->oSmarty->fetch('tpl_inc/widgets/help.tpl');
    }
}
