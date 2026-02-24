<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Backend;

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Shop;
use stdClass;

/**
 * Class CustomExports
 * @package Plugin\jtl_google_shopping\Backend
 */
class CustomExports extends CustomLink
{
    private string $stepPlugin = 'sprachen';

    protected function controller(): bool
    {
        Shop::Container()->getGetText()->loadPluginLocale('custom_exports', $this->getPlugin());
        $stepPlugin = $this->getRequestValue('stepPlugin');
        if ($stepPlugin === $this->stepPlugin && Form::validateToken()) {
            if ($this->getRequestValue('btn_save_new', '') !== '') {
                $this->insert();
            } elseif ($this->getRequestValue('btn_delete', '') !== '') {
                $this->delete();
            }
        }
        $this->show();

        return true;
    }

    protected function getTemplate(): string
    {
        return 'templates/custom_exports.tpl';
    }

    private function validate(stdClass $data): bool
    {
        $alerts = Shop::Container()->getAlertService();
        $result = true;
        if (empty($data->cName)) {
            $alerts->addError(
                \sprintf(\__('Field can not be empty'), \__('Export name')),
                'lang_validate_name'
            );
            $result = false;
        }
        if (empty($data->cDateiname)) {
            $alerts->addError(
                \sprintf(\__('Field can not be empty'), \__('File name')),
                'lang_validate_filename'
            );
            $result = false;
        } elseif (
            $data->cContent === 'PluginContentFile_googleShopping.php'
            && !\str_contains($data->cDateiname, '.zip')
        ) {
            $alerts->addError(\__('File name must contain .zip extension'), 'lang_validate_extension');
            $result = false;
        } elseif (
            $data->cContent === 'PluginContentFile_googleReview.php'
            && !\str_contains($data->cDateiname, '.xml')
        ) {
            $alerts->addError(\__('File name must contain .xml extension'), 'lang_validate_extension');
            $result = false;
        }
        if (empty($data->kSprache)) {
            $alerts->addError(\sprintf(\__('Field can not be empty'), \__('Language')), 'lang_validate_language');
            $result = false;
        }
        if (empty($data->kKundengruppe)) {
            $alerts->addError(
                \sprintf(\__('Field can not be empty'), \__('Customer group')),
                'lang_validate_customergroup'
            );
            $result = false;
        }
        if (empty($data->kWaehrung)) {
            $alerts->addError(
                \sprintf(\__('Field can not be empty'), \__('Currency')),
                'lang_validate_currency'
            );
            $result = false;
        }

        return $result;
    }

    private function insert(): void
    {
        $export                  = new stdClass();
        $export->cName           = \trim($this->getRequestValue('cName', ''));
        $export->cDateiname      = \trim($this->getRequestValue('cDateiname', ''));
        $export->kSprache        = (int)$this->getRequestValue('kSprache', 0);
        $export->kKundengruppe   = (int)$this->getRequestValue('kKundengruppe', 0);
        $export->kWaehrung       = (int)$this->getRequestValue('kWaehrung', 0);
        $export->kPlugin         = $this->getPlugin()->getID();
        $export->cKopfzeile      = ' ';
        $export->cContent        = (int)$this->getRequestValue('isReviewFeed', 0) === 0
            ? 'PluginContentFile_googleShopping.php'
            : 'PluginContentFile_googleReview.php';
        $export->cFusszeile      = ' ';
        $export->async           = 1;
        $export->nUseCache       = 1;
        $export->cKodierung      = 'UTF-8noBOM';
        $export->nVarKombiOption = 2;

        if (!$this->validate($export)) {
            return;
        }
        $insertedID = $this->db->insert('texportformat', $export);
        if ($insertedID === 0) {
            Shop::Container()->getAlertService()->addError(
                \sprintf(\__('Export format could not be...'), \__('created')),
                'export_insert'
            );

            return;
        }
        $configValues = $this->db->selectAll(
            'teinstellungenconf',
            'kEinstellungenSektion',
            \CONF_EXPORTFORMATE,
            'cWertName'
        );

        foreach ($configValues as $configValue) {
            $formatSettings                = new stdClass();
            $formatSettings->kExportformat = $insertedID;
            $formatSettings->cName         = $configValue->cWertName;
            $formatSettings->cWert         = 'N';
            if ($configValue->cWertName === 'exportformate_lieferland') {
                $formatSettings->cWert = \trim($this->getRequestValue('cLieferlandIso', ''));
            }
            $this->db->insert('texportformateinstellungen', $formatSettings);
        }

        $this->setRequestData([]);
        Shop::Container()->getAlertService()->addSuccess(
            \sprintf(\__('Export format would be successfully...'), \__('created')),
            'export_insert'
        );
    }

    private function delete(): void
    {
        $exportID = (int)$this->getRequestValue('btn_delete');
        if ($exportID > 0) {
            $this->db->delete('texportformateinstellungen', 'kExportformat', $exportID);
            $this->db->delete('texportformat', 'kExportformat', $exportID);
            $this->setRequestData([]);
            Shop::Container()->getAlertService()->addSuccess(
                \sprintf(\__('Export format would be successfully...'), \__('deleted')),
                'export_delete'
            );
        } else {
            Shop::Container()->getAlertService()->addError(
                \sprintf(\__('Export format could not be...'), \__('deleted')),
                'export_delete'
            );
        }
    }

    private function show(): void
    {
        $languages         = $this->db->selectAll('tsprache', [], [], 'kSprache, cNameDeutsch', 'cNameDeutsch');
        $customerGroups    = $this->db->selectAll('tkundengruppe', [], [], 'kKundengruppe, cName', 'cName');
        $currencies        = $this->db->selectAll('twaehrung', [], [], 'kWaehrung, cName', 'cName');
        $deliveryMethods   = $this->db->selectAll('tversandart', [], [], 'cLaender');
        $shippingCountries = '';
        foreach ($deliveryMethods as $deliveryMethod) {
            $shippingCountries .= ' ' . $deliveryMethod->cLaender;
        }
        $shippingCountries = \array_unique(\explode(' ', $shippingCountries));
        \sort($shippingCountries);

        $exportFormats = $this->db->queryPrepared(
            "SELECT
                texportformat.kExportformat,
                texportformat.cName,
                texportformat.cDateiname,
                texportformateinstellungen.cWert AS cLieferlandIso,
                tsprache.cNameDeutsch AS cSprache,
                tkundengruppe.cName AS cKundengruppe,
                twaehrung.cName AS cWaehrung
                FROM texportformat
                LEFT JOIN texportformateinstellungen
                    ON texportformat.kExportformat = texportformateinstellungen.kExportformat
                        AND texportformateinstellungen.cName = 'exportformate_lieferland'
                LEFT JOIN tsprache
                    ON texportformat.kSprache = tsprache.kSprache
                LEFT JOIN tkundengruppe
                    ON texportformat.kKundengruppe = tkundengruppe.kKundengruppe
                LEFT JOIN twaehrung
                    ON texportformat.kWaehrung = twaehrung.kWaehrung
                WHERE kPlugin = :pluginID",
            ['pluginID' => $this->getPlugin()->getID()],
            ReturnType::ARRAY_OF_OBJECTS
        );

        Shop::Smarty()
            ->assign('oExportformate', $exportFormats)
            ->assign('gs_languages', $languages)
            ->assign('gs_customerGroups', $customerGroups)
            ->assign('gs_currencies', $currencies)
            ->assign('gs_shippingCountries', $shippingCountries)
            ->assign('stepPlugin', $this->stepPlugin);
    }
}
