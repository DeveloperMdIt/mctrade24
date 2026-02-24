<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\adminmenu\CarrierMapping;

/**
 * Class DeleteCarrierMapping
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class DeleteCarrierMappingController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $alertService = $this->getAlertService();
        if ((new CarrierMapping($this->getDB()))->deleteMapping(Request::postInt('id')) >= 0) {
            $alertService->addSuccess(
                \__('Carrier-Mapping gelöscht'),
                'carrierMappingSaved'
            );
        } else {
            $alertService->addError(
                \__('Carrier-Mapping konnte nicht gelöscht werden'),
                'carrierMappingFailed'
            );
        }

        $this->redirectSelf();
    }
}
