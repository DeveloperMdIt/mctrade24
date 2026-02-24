<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\adminmenu\CarrierMapping;

/**
 * Class SaveCarrierMappingController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class SaveCarrierMappingController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $alertService = $this->getAlertService();
        if (
            (new CarrierMapping($this->getDB()))->addMapping(
                Request::postInt('id'),
                Request::postVar('carrier_wawi'),
                Request::postVar('carrier_paypal')
            ) >= 0
        ) {
            $alertService->addSuccess(
                \__('Carrier-Mapping gespeichert'),
                'carrierMappingSaved'
            );
        } else {
            $alertService->addError(
                \__('Carrier-Mapping konnte nicht gespeichert werden'),
                'carrierMappingFailed'
            );
        }

        $this->redirectSelf();
    }
}
