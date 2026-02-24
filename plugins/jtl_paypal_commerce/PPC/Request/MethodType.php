<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

/**
 * Class MethodType
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class MethodType
{
    public const POST   = 'POST';
    public const GET    = 'GET';
    public const HEAD   = 'HEAD';
    public const PATCH  = 'PATCH';
    public const DELETE = 'DELETE';
}
