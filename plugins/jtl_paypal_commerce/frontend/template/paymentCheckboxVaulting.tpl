{checkbox id="ppc_vaulting_enable_{$fundingSource}"
name="ppc_vaulting_enable[{$fundingSource}]" value="1" checked=$vaulting_enabled
}
{$label_vaulting_enable}{if isset($label_vaulting_tooltip)}
    <span data-html="true" data-toggle="tooltip" data-placement="right" title="{$label_vaulting_tooltip|escape:"html"}" data-original-title="{$label_vaulting_tooltip|escape:"html"}">
        <span class="fas fa-info-circle fa-fw"></span>
    </span>{/if}
{/checkbox}
