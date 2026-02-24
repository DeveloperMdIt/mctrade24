{if !empty($am_article->FunktionsAttribute.admorris_pro_eu_energy_label)}
    {$scaleAndValueArr = $euEnergyLabels->getEnergyScaleAndValue($am_article->FunktionsAttribute.admorris_pro_eu_energy_label)}
    {$Artikel = $am_article}
{elseif !empty($Artikel->FunktionsAttribute.admorris_pro_eu_energy_label)}
    {$scaleAndValueArr = $euEnergyLabels->getEnergyScaleAndValue($Artikel->FunktionsAttribute.admorris_pro_eu_energy_label)}
{else}
    {$scaleAndValueArr = null}
{/if}
{if $scaleAndValueArr != null}
    {* scaleStart can be an array and has to be converted to a string for the screenreader text usage of sprintf *}
    {if is_array($scaleAndValueArr['scaleStart'])}
      {$scaleStart = $scaleAndValueArr['scaleStart'][0]|cat:$scaleAndValueArr['scaleStart'][1]}
    {else}
      {$scaleStart = $scaleAndValueArr['scaleStart']}
    {/if}
    {$bgColor = $euEnergyLabels->getEnergyBgFromScaleAndValue($scaleAndValueArr)}
    {* Set display:none until the stylesheet is loaded and overrides it *}
    {$labelImage = $Artikel->FunktionsAttribute.admorris_pro_eu_energy_label_image|default:false}
    {if !empty($labelImage)}
      {$el = 'button type="button"'}
      {$imgAltText = $Artikel->FunktionsAttribute.admorris_pro_eu_energy_label_image_alt_text|default:''}

    {else}
      {$el = 'div'}
    {/if}

  {* if there is an image use a button to open the modal for showing the image *}
  <{$el} class="eu-energy-label{if !empty($labelImage)} eu-energy-label--button{/if}"
    {if !empty($labelImage)} onclick="openEuEnergyLabelModal('{$labelImage}', '{$imgAltText}')"{/if}>
        <div class="eu-energy-label-value eu-energy-label-value--{$bgColor}">
            <div class="eu-energy-label-scale" aria-hidden="true">
                {if is_array($scaleAndValueArr['scaleStart'])}
                    <span>{$scaleAndValueArr['scaleStart'][0]}<sup class="sup-vertical">{$scaleAndValueArr['scaleStart'][1]}</sup></span>
                {else}
                    <span>{$scaleAndValueArr['scaleStart']}</span>
                {/if}
                <span class="eu-energy-label-vertical-splitter"><svg class="eu-energy-label-scale-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 37"><path fill="#000" d="M11.856 0 0 14.625h8.6V37h6.8V14.625H24L11.856 0Z"/></svg></span>
                <span>{$scaleAndValueArr['scaleEnd']}</span>
            </div>
            <div class="sr-only">{$admUtils::trans('marketing_energy_efficiency_class')}</div>
            {if is_array($scaleAndValueArr['value'])}
                <div class="eu-energy-label-text eu-energy-label-text--{$euEnergyLabels->list_position}">{$scaleAndValueArr['value'][0]}<sup class="sup-large">{$scaleAndValueArr['value'][1]}</sup></div>
            {else}
                <div class="eu-energy-label-text eu-energy-label-text--{$euEnergyLabels->list_position}">{$scaleAndValueArr['value']}</div>
            {/if}
        </div>
        <div class="sr-only"> ({sprintf($admUtils::trans('marketing_energy_scale_sr_text'), $scaleStart, $scaleAndValueArr['scaleEnd'])})</div>
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="eu-energy-label-arrow eu-energy-label-arrow--{$bgColor} svg-triangle" viewBox="0 0 22.313 45" xml:space="preserve"><path d="M22 22 0 45V0z"/><path class="svg-triangle__stroke" d="m0 45 22-23L0 0"/></svg>
    </{$el}>
{/if}