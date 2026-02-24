<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\frontend;

use JTL\Catalog\Product\Artikel;
use JTL\Filter\SearchResultsInterface;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_gpsr\adminmenu\Config;
use Plugin\jtl_gpsr\gpsr\GPSR;

/**
 * Class Handler
 * @package Plugin\jtl_gpsr\frontend
 */
class Handler
{
    private Config $config;

    private PluginInterface $plugin;

    private JTLSmarty $smarty;

    /**
     * Handler constructor
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
        $this->config = new Config($plugin, Shop::Container()->getDB());
    }

    /**
     * @param array{smarty: JTLSmarty} $args
     * @return void
     */
    public function smartyInc(array $args): void
    {
        $this->smarty = $args['smarty'];
    }

    private function assignDefault(): void
    {
        $lang              = $this->plugin->getLocalization();
        $manufacturer      = $this->config->getValue('gpsr_template_manufacturer') ?? '';
        $responsiblePerson = $this->config->getValue('gpsr_template_responsibleperson') ?? '';

        $this->smarty->assign('langGPSRManufacturerLabel', $lang->getTranslation('jtl_gpsr_ManufacturerLabel') ?? '')
                     ->assign('langGPSRRespPersonLabel', $lang->getTranslation('jtl_gpsr_RespPersonLabel') ?? '')
                     ->assign('langGPSRHeading', $lang->getTranslation('jtl_gpsr_SafetyInformationHeading') ?? '')
                     ->assign('gpsrManufacturer', $manufacturer)
                     ->assign('gpsrResponsiblePerson', $responsiblePerson);
    }

    /**
     * @param array{oArtikel: Artikel} $args
     * @return void
     */
    public function pageProduct(array $args): void
    {
        if ($this->config->getValue('gpsr_productdetails_show') !== 'on') {
            return;
        }

        $gpsr = new GPSR([$args['oArtikel']]);
        if (!$gpsr->hasData()) {
            return;
        }

        $gpsr->assignData($this->smarty);
        $this->assignDefault();

        $this->smarty->assign('gprsTemplateBlock', $this->config->getValue('gpsr_templateblock_productdetails'));
    }

    /**
     * @return void
     */
    public function pageProductList(): void
    {
        if ($this->config->getValue('gpsr_productlist_show') !== 'on') {
            return;
        }

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->smarty->getTemplateVars('Suchergebnisse');
        $gpsr          = new GPSR($searchResults->getProducts());
        if (!$gpsr->hasData()) {
            return;
        }

        $gpsr->assignData($this->smarty);
        $this->assignDefault();

        $this->smarty->assign('gprsTemplateBlock', $this->config->getValue('gpsr_templateblock_productlist'));
    }
}
