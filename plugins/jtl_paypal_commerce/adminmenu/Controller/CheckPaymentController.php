<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;

/**
 * Class CheckPaymentController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class CheckPaymentController extends AbstractController
{
    public function run(): void
    {
        $factory = Helper::getInstance($this->getPlugin());
        if (($paymentID = Request::getInt('kZahlungsart')) === 0) {
            $paymentID = Request::postInt('kZahlungsart');
        }
        if ($paymentID > 0 && ($paymentMethod = $factory->getPaymentFromID($paymentID)) !== null) {
            $method = $factory->getMethodFromID($paymentID);
            if ($method !== null) {
                $paymentMethod->validatePaymentConfiguration($method);
            }
        }
    }
}
