{block name="tabs-gpsr-info"}
    {if !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_name"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_street"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_housenumber"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_postalcode"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_city"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_state"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_country"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_email"]) || !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_homepage"])}
        <div class="gpsr-info gpsr-info-manufacturer">
            <h2 class="h5">{lang key='manufacturer-info' section='custom'}:</h2>
            <div>{$Artikel->FunktionsAttribute["gpsr_manufacturer_name"]|default:''}</div>
            <div>{$Artikel->FunktionsAttribute["gpsr_manufacturer_street"]|default:''} {$Artikel->FunktionsAttribute["gpsr_manufacturer_housenumber"]|default:''}</div>
            <div>{$Artikel->FunktionsAttribute["gpsr_manufacturer_postalcode"]|default:''} {$Artikel->FunktionsAttribute["gpsr_manufacturer_city"]|default:''}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_state"])}{$Artikel->FunktionsAttribute["gpsr_manufacturer_state"]}, {/if}{$Artikel->FunktionsAttribute["gpsr_manufacturer_country"]|default:''}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_email"])}<a href="mailto:{$Artikel->FunktionsAttribute["gpsr_manufacturer_email"]}">{$Artikel->FunktionsAttribute["gpsr_manufacturer_email"]}</a>{/if}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_manufacturer_homepage"])}<a href="{$Artikel->FunktionsAttribute["gpsr_manufacturer_homepage"]}" target="_blank">{$Artikel->FunktionsAttribute["gpsr_manufacturer_homepage"]}</a>{/if}</div>
        </div>
    {/if}
    {if !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_name"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_street"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_housenumber"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_postalcode"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_city"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_state"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_country"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_email"]) || !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_homepage"])}
        <div class="gpsr-info gpsr-info-responsible-person">
            <h2 class="h5">{lang key='responsible-person' section='custom'}:</h2>
            <div>{$Artikel->FunktionsAttribute["gpsr_responsibleperson_name"]|default:''}</div>
            <div>{$Artikel->FunktionsAttribute["gpsr_responsibleperson_street"]|default:''} {$Artikel->FunktionsAttribute["gpsr_responsibleperson_housenumber"]|default:''}</div>
            <div>{$Artikel->FunktionsAttribute["gpsr_responsibleperson_postalcode"]|default:''} {$Artikel->FunktionsAttribute["gpsr_responsibleperson_city"]|default:''}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_state"])}{$Artikel->FunktionsAttribute["gpsr_responsibleperson_state"]}, {/if}{$Artikel->FunktionsAttribute["gpsr_responsibleperson_country"]|default:''}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_email"])}<a href="mailto:{$Artikel->FunktionsAttribute["gpsr_responsibleperson_email"]}">{$Artikel->FunktionsAttribute["gpsr_responsibleperson_email"]}</a>{/if}</div>
            <div>{if !empty($Artikel->FunktionsAttribute["gpsr_responsibleperson_homepage"])}<a href="{$Artikel->FunktionsAttribute["gpsr_responsibleperson_homepage"]}" target="_blank">{$Artikel->FunktionsAttribute["gpsr_responsibleperson_homepage"]}</a>{/if}</div>
        </div>
    {/if}
{/block}