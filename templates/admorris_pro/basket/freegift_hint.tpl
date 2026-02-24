{block name='basket-freegift-hint'}
    {* INFO: fallback using getFreeGiftService for Versions below 5.5.3  *}
    {if $Einstellungen.sonstiges.sonstiges_gratisgeschenk_wk_hinweis_anzeigen === 'Y'
    && isset($freeGifts) && $freeGifts->count() > 0
    || $Einstellungen.sonstiges.sonstiges_gratisgeschenk_nutzen === 'Y' && JTL\Shop::Container()->getFreeGiftService()->getFreeGifts()->count() > 0} 
        {$freeGiftService = JTL\Shop::Container()->getFreeGiftService()}
        {if $freeGiftService->basketHoldsFreeGift(JTL\Session\Frontend::getCart()) === false}
            
            <div class="free-gift-hint media-object">
                {if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_GRATISGESCHENK])}
                    <a class="media-object__asset" href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_GRATISGESCHENK]->getURL()}"
                        title="{lang key='freeGiftsSeeAll' section='basket'}"><i class="fas fa-gifts"></i></a>
                {else}
                    <i class="media-object__asset fas fa-gifts"></i>
                {/if}
                <div>
                    <span class="font-weight-bold">{lang key='freeGiftsAvailable' section='basket'}</span>
                    <span class="d-block">{lang section='basket' key='freeGiftsAvailableText'}</span>
                    <a href="{get_static_route id='warenkorb.php'}#freeGiftsHeading" class="btn btn-link p-0">
                        <u>{lang section='basket' key='chooseFreeGiftNow'}</u>
                    </a>
                    {block name='basket-freegift-hint-still-missing-amount'}
                        {if $Einstellungen.sonstiges.sonstiges_gratisgeschenk_noch_nicht_verfuegbar_anzeigen === 'Y'
                        && !empty($nextFreeGiftMissingAmount)}
                            <span class="d-block">{lang section='basket' key='freeGiftsStillMissingAmountForNextFreeGift'
                            printf=JTL\Catalog\Product\Preise::getLocalizedPriceString($nextFreeGiftMissingAmount)}</span>
                        {/if}
                    {/block}
                </div>
            </div>
        {/if}
    {/if}
{/block}
