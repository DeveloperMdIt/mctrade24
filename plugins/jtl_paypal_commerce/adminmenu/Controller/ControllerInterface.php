<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Exception;

interface ControllerInterface
{
    /**
     * @throws Exception
     */
    public function run(): void;
}
