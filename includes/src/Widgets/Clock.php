<?php

declare(strict_types=1);

namespace JTL\Widgets;

/**
 * Class Clock
 * @package JTL\Widgets
 */
class Clock extends AbstractWidget
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
        return $this->oSmarty->fetch('tpl_inc/widgets/clock.tpl');
    }
}
