<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;

interface RendererInterface
{
    /**
     * @throws TabNotAvailException
     */
    public function checkRendering(bool $force = false): void;

    /**
     * @throws TabNotAvailException
     */
    public function render(JTLSmarty $smarty): void;
}
