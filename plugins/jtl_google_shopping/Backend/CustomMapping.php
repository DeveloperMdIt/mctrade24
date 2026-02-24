<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Backend;

use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Shop;
use stdClass;

/**
 * Class CustomMapping
 * @package Plugin\jtl_google_shopping\Backend
 */
class CustomMapping extends CustomLink
{
    private string $stepPlugin = 'mapping';

    protected function controller(): bool
    {
        Shop::Container()->getGetText()->loadPluginLocale('custom_mapping', $this->getPlugin());
        $stepPlugin = $this->getRequestValue('stepPlugin');
        if ($stepPlugin === $this->stepPlugin && Form::validateToken()) {
            if ($this->getRequestValue('btn_delete') !== null) {
                $this->delete();
            } elseif ($this->getRequestValue('btn_save_new') !== null) {
                $this->insert();
            }
        }
        $this->show();

        return true;
    }

    protected function getTemplate(): string
    {
        return 'templates/custom_mapping.tpl';
    }

    private function validate(stdClass $data): bool
    {
        $alerts = Shop::Container()->getAlertService();
        $result = true;
        if (
            empty($data->cType)
            || empty($data->cVon)
            || empty($data->cZu)
        ) {
            $alerts->addError(\__('Empty fields can not be stored'), 'mapping_validate_value');
            $result = false;
        }

        return $result;
    }

    private function insert(): void
    {
        $mapping        = new stdClass();
        $mapping->cType = \trim($this->getRequestValue('cType', ''));
        $mapping->cVon  = \mb_strtolower(\trim($this->getRequestValue('cVon', '')));
        $mapping->cZu   = \mb_strtolower(\trim($this->getRequestValue('cZu' . $mapping->cType, '')));

        if (!$this->validate($mapping)) {
            return;
        }
        if ($this->db->insert('xplugin_jtl_google_shopping_mapping', $mapping) > 0) {
            $this->setRequestData([]);
            Shop::Container()->getAlertService()->addSuccess(
                \__('Mapping would be successfully added'),
                'mapping_insert'
            );
        } else {
            Shop::Container()->getAlertService()->addError(\__('Mapping could not be added'), 'mapping_insert');
        }
    }

    private function delete(): void
    {
        $mappingID = (int)\key($this->getRequestValue('btn_delete'));
        $affected  = $this->db->delete('xplugin_jtl_google_shopping_mapping', 'kMapping', $mappingID);
        if ($affected < 0) {
            Shop::Container()->getAlertService()->addError(
                \sprintf(\__('Attribute with ID could not be ...'), $mappingID, \__('deleted')),
                'mapping_delete'
            );
        } elseif ($affected > 0) {
            Shop::Container()->getAlertService()->addSuccess(
                \sprintf(\__('Mapping with ID successfully ...'), $mappingID, \__('deleted')),
                'mapping_delete'
            );
        }
    }

    private function show(): void
    {
        $mappings = $this->db->query(
            'SELECT * FROM xplugin_jtl_google_shopping_mapping ORDER BY cType, cZu',
            ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );

        Shop::Smarty()
            ->assign('mappings', $mappings)
            ->assign('stepPlugin', $this->stepPlugin);
    }
}
