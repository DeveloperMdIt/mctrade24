<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Backend;

use JTL\Helpers\Form;
use JTL\Shop;
use stdClass;

/**
 * Class CustomAttributes
 * @package Plugin\jtl_google_shopping\Backend
 */
class CustomAttributes extends CustomLink
{
    private string $stepPlugin = 'attribute';

    protected function controller(): bool
    {
        Shop::Container()->getGetText()->loadPluginLocale('custom_attributes', $this->getPlugin());
        $this->stepPlugin = $this->getRequestValue('stepPlugin', 'attribute');
        if (($this->stepPlugin === 'neuesAttr' || $this->stepPlugin === 'alteAttr') && Form::validateToken()) {
            if ($this->getRequestValue('btn_delete') !== null) {
                $this->delete();
            } elseif ($this->getRequestValue('btn_standard') !== null) {
                $this->reset();
            } elseif ($this->getRequestValue('btn_reset_all') !== null) {
                $this->resetAll();
            } elseif ($this->stepPlugin === 'neuesAttr') {
                $this->insert();
            } elseif ($this->stepPlugin === 'alteAttr') {
                $this->update();
            }
        }
        $this->show();

        return true;
    }

    protected function getTemplate(): string
    {
        return 'templates/custom_attributes.tpl';
    }

    /**
     * @return array<string, string>
     */
    private function getAttributeSrc(): array
    {
        return [
            \__('Article property')   => 'ArtikelEigenschaft',
            \__('Function attribute') => 'FunktionsAttribut',
            \__('Attribute')          => 'Attribut',
            \__('Feature')            => 'Merkmal',
            \__('Static value')       => 'WertName',
            \__('Parent attribute')   => 'VaterAttribut'
        ];
    }

    private function validate(object $data): bool
    {
        $alerts = Shop::Container()->getAlertService();
        $result = true;

        if ($data->bStandard === 0 && (!isset($data->cGoogleName) || \mb_strlen($data->cGoogleName) === 0)) {
            $alerts->addError(
                \sprintf(\__('A valid value for this must be entered'), \__('Google name')),
                'attribute_validate_GoogleName'
            );
            $result = false;
        }

        if (isset($data->eWertHerkunft) && $data->eWertHerkunft !== 'VaterAttribut' && !isset($data->cWertName)) {
            $alerts->addError(
                \sprintf(\__('A valid value for this must be entered'), \__('Value name')),
                'attribute_validate_WertName'
            );
            $result = false;
        }
        if (!isset($data->eWertHerkunft) || !\in_array($data->eWertHerkunft, $this->getAttributeSrc(), true)) {
            $alerts->addError(
                \sprintf(\__('A valid value for this must be entered'), \__('Value type')),
                'attribute_validate_Werttyp'
            );
            $result = false;
        }
        if (($data->kVaterAttribut ?? 0) > 0) {
            $attribute = $this->db->select(
                'xplugin_jtl_google_shopping_attribut',
                'kAttribut',
                $data->kVaterAttribut
            );
            if ($attribute !== null && $attribute->eWertHerkunft !== 'VaterAttribut') {
                $alerts->addError(
                    \__('Only IDs are allowed for which the value type Parent attribute is selected'),
                    'attribute_validate_VaterAttribut'
                );
                $result = false;
            }
            if ($data->eWertHerkunft === 'VaterAttribut') {
                $alerts->addError(
                    \__('Leave this blank, if this attribute is not a child attribute'),
                    'attribute_validate_VaterAttribut'
                );
                $result = false;
            }
        }

        return $result;
    }

    private function delete(): void
    {
        $attributeID = (int)\key($this->getRequestValue('btn_delete'));
        $affected    = $this->db->getAffectedRows(
            'DELETE FROM xplugin_jtl_google_shopping_attribut
                WHERE bStandard != 1
                    AND kAttribut = :attributeID',
            ['attributeID' => $attributeID]
        );
        if ($affected < 0) {
            Shop::Container()->getAlertService()->addError(
                \sprintf(\__('Attribute with ID could not be ...'), $attributeID, \__('deleted')),
                'attribute_delete'
            );
        } elseif ($affected > 0) {
            Shop::Container()->getAlertService()->addSuccess(
                \sprintf(\__('Attribute with ID successfully ...'), $attributeID, \__('deleted')),
                'attribute_delete'
            );
        }

        $this->setRequestData([]);
    }

    private function reset(): void
    {
        $attributeID = (int)\key($this->getRequestValue('btn_standard'));
        $affected    = $this->db->getAffectedRows(
            'UPDATE xplugin_jtl_google_shopping_attribut
                SET
                    cGoogleName    = cStandardGoogleName,
                    cWertName      = cStandardWertName,
                    eWertHerkunft  = eStandardWertHerkunft,
                    kVaterAttribut = kStandardVaterAttribut
                WHERE bStandard = 1
                    AND kAttribut = :attributeID',
            ['attributeID' => $attributeID]
        );
        if ($affected < 0) {
            Shop::Container()->getAlertService()->addError(
                \sprintf(\__('Attribute with ID could not be ...'), $attributeID, \__('reset')),
                'attribute_reset'
            );
        } elseif ($affected > 0) {
            Shop::Container()->getAlertService()->addSuccess(
                \sprintf(\__('Attribute with ID successfully ...'), $attributeID, \__('reset')),
                'attribute_reset'
            );
        }

        $this->setRequestData([]);
    }

    private function resetAll(): void
    {
        $this->db->query('TRUNCATE TABLE xplugin_jtl_google_shopping_attribut');
        $installer = new Installer($this->getPlugin(), $this->db);
        if ($installer->installAttributeData()) {
            Shop::Container()->getAlertService()->addSuccess(
                \__('All attributes would be successfully reset to default values'),
                'attribute_reset_all'
            );
        } else {
            Shop::Container()->getAlertService()->addError(
                \__('Attributes could not be reset to default values'),
                'attribute_reset_all'
            );
        }
    }

    private function insert(): void
    {
        $attribute                 = new stdClass();
        $attribute->cGoogleName    = \trim($this->getRequestValue('cGoogleName'));
        $attribute->cWertName      = \trim($this->getRequestValue('cWertName'));
        $attribute->eWertHerkunft  = \trim($this->getRequestValue('eWertHerkunft'));
        $attribute->kVaterAttribut = (int)$this->getRequestValue('kVaterAttribut', 0);
        $attribute->bAktiv         = $this->getRequestValue('bAktiv') !== null ? 1 : 0;
        $attribute->bStandard      = 0;

        if ($this->validate($attribute)) {
            if ($this->db->insert('xplugin_jtl_google_shopping_attribut', $attribute) > 0) {
                $this->setRequestData([]);
                Shop::Container()->getAlertService()->addSuccess(
                    \__('Attribute would be successfully added'),
                    'attribute_insert'
                );
            } else {
                Shop::Container()->getAlertService()->addError(
                    \__('Attribute could not be added'),
                    'attribute_insert'
                );
            }
        }
    }

    private function update(): void
    {
        foreach ($this->getRequestValue('eWertHerkunft', []) as $key => $value) {
            $attribute       = new stdClass();
            $googleName      = \trim($this->getRequestValue('cGoogleName', [$key => ''])[$key] ?? '');
            $updateKeys      = ['kAttribut'];
            $updateKeyValues = [(int)$key];
            if (\strlen($googleName) > 0) {
                $attribute->cGoogleName    = $googleName;
                $attribute->kVaterAttribut = (int)$this->getRequestValue('kVaterAttribut', [$key => '0'])[$key];
                $attribute->bStandard      = 0;
            } else {
                $attribute->bStandard = 1;
                $updateKeys[]         = 'bStandard';
                $updateKeyValues[]    = 1;
            }

            $attribute->cWertName     = \trim($this->getRequestValue('cWertName', [$key => ''])[$key]);
            $attribute->eWertHerkunft = \trim($this->getRequestValue('eWertHerkunft', [$key => ''])[$key]);
            $attribute->bAktiv        = ($this->getRequestValue('bAktiv', [$key => null])[$key] ?? null) !== null
                ? 1
                : 0;

            if (!$this->validate($attribute)) {
                continue;
            }
            $affected = $this->db->update(
                'xplugin_jtl_google_shopping_attribut',
                $updateKeys,
                $updateKeyValues,
                $attribute
            );

            if ($affected < 0) {
                Shop::Container()->getAlertService()->addError(
                    \sprintf(\__('Attribute with ID could not be ...'), $key, \__('changed')),
                    'attribute_update_' . $key
                );
            } elseif ($affected > 0) {
                Shop::Container()->getAlertService()->addSuccess(
                    \sprintf(\__('Attribute with ID successfully ...'), $key, \__('changed')),
                    'attribute_update_' . $key
                );
            }
        }

        $this->setRequestData([]);
    }

    private function show(): void
    {
        $allChildAttributes = [];
        $attributes         = $this->db->getObjects(
            'SELECT * FROM xplugin_jtl_google_shopping_attribut
                WHERE kVaterAttribut = 0
                ORDER BY kAttribut'
        );
        $childAttributes    = $this->db->getObjects(
            'SELECT * FROM xplugin_jtl_google_shopping_attribut
                WHERE kVaterAttribut != 0
                ORDER BY kAttribut'
        );

        foreach ($childAttributes as $childAttribute) {
            if (\is_array($allChildAttributes[$childAttribute->kVaterAttribut] ?? null)) {
                $allChildAttributes[$childAttribute->kVaterAttribut][] = $childAttribute;
            } else {
                $allChildAttributes[$childAttribute->kVaterAttribut] = [$childAttribute];
            }
        }

        Shop::Smarty()->assign('attribute_arr', $attributes)
            ->assign('kindAttribute_arr', $allChildAttributes)
            ->assign('eWertHerkunft_arr', $this->getAttributeSrc())
            ->assign('stepPlugin', $this->stepPlugin);
    }
}
