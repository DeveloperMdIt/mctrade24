{if $type === 'delivery_fili' && $nFiliCount>0}
    <h4>{sprintf($jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_info'), $nFiliCount, $jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_postfiliale'))}</h4>
{/if}
{if $type === 'delivery_pack' && $nPackCount>0}
    <h4>{sprintf($jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_info'), $nPackCount, $jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_packstation'))}</h4>
{/if}
{assign var="no_results" value=false}
<table class="table table-striped">
    <thead>
    <tr>
        <th>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_address')}</th>
        <th>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_distance')}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody>
    {if $type === 'delivery_fili' || $type === 'delivery_pack'}
        {if $type === 'delivery_pack' && $nPackCount > 0}
            {foreach $locations as $location}
                {if $location->keyWord === 'Packstation'}
                    <tr>
                        <td>
                            {if !is_null($location->shopName) && $location->shopName !== ''}{$location->shopName}<br /> {/if}
                            {$location->street}<br /> {$location->zipCode} {$location->district}
                        </td>
                        <td>
                            {math equation="x/y" x=$location->geoPosition->distance y=1000 format="%.2f"} km
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success dhlData" data-street="{$location->keyWord}" data-number="{$location->packstationId}" data-zip="{$location->zipCode}" data-city="{$location->district}">
                                {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_usedata')}
                            </button>
                        </td>
                    </tr>
                {/if}
            {/foreach}
        {else}
            {if $type === 'delivery_pack'}
                {assign var='no_results' value=true}
            {/if}
        {/if}
        {if $type === 'delivery_fili' && $nFiliCount > 0}
            {foreach $locations as $location}
                {if $location->keyWord === 'Postfiliale'}
                    <tr>
                        <td>
                            {if $location->shopType === 'dhlpaketshop'}
                                {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_dhlpaketshop')}<br />
                            {/if}
                            {if $location->shopType === 'retailoutlet'}
                                {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_postoffice')}<br />
                            {/if}
                            {if !is_null($location->shopName) && $location->shopName !== ''}{$location->shopName}<br /> {/if}
                            {$location->street}<br /> {$location->zipCode} {$location->district}
                        </td>
                        <td>
                            {math equation="x/y" x=$location->geoPosition->distance y=1000 format="%.2f"} km
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success dhlData" data-street="{$location->keyWord}" data-number="{$location->depotServiceNo}" data-zip="{$location->zipCode}" data-city="{$location->district}">
                                {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_usedata')}
                            </button>
                        </td>
                    </tr>
                {/if}
            {/foreach}
        {else}
            {if $type === 'delivery_fili'}
                {assign var='no_results' value=true}
            {/if}
        {/if}
    {else}
        {assign var='no_results' value=true}
    {/if}

    {if isset($no_results) && $no_results == true}
        <tr>
            <td colspan="3">{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_noresult')}</td>
        </tr>
    {/if}
    <tr>
        <td colspan="3">{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_result_advice')}</td>
    </tr>
    </tbody>
</table>
