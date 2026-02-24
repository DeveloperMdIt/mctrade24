{if $isNova}
    {block name="layout-footer-content" append}
        {modal id="deliverySpots" title=$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_header')}
            <div class="dhl_loader" style="display: none;">Loading...</div>
            <div id="locationList">

            </div>
        {/modal}
    {/block}
{else}
    {block name="main-wrapper-closingtag" prepend}
        {modal id="deliverySpots" title=$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_search_header')}
            <div class="dhl_loader" style="display: none;">Loading...</div>
            <div id="locationList">

            </div>
        {/modal}
    {/block}
{/if}
