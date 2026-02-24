{if $isNova}
    {block name="checkout-step5-confirmation-shipping-method" append}
        {if isset($smarty.session.wunschtag_selected) && $smarty.session.wunschtag_selected !== ''}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschtag')}</strong>: {$smarty.session.wunschtag_selected}
            </p>
        {/if}
        {if !empty($smarty.session.wunschlocation)}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschort')}</strong>: {$smarty.session.wunschlocation}
            </p>
        {/if}
    {/block}
{else}
    {block name="checkout-step5-confirmation-shipping-method" append}
        {if isset($smarty.session.wunschtag_selected) && $smarty.session.wunschtag_selected !== ''}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschtag')}</strong>: {$smarty.session.wunschtag_selected}
            </p>
        {/if}
        {if !empty($smarty.session.wunschlocation)}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschort')}</strong>: {$smarty.session.wunschlocation}
            </p>
        {/if}
    {/block}

    {block name="checkout-confirmation-shipping-method" append}
        <br /><br />
        {if isset($smarty.session.wunschtag_selected) && $smarty.session.wunschtag_selected !== ''}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschtag')}</strong>: {$smarty.session.wunschtag_selected}
            </p>
        {/if}
        {if !empty($smarty.session.wunschlocation)}
            <p class="small text-muted">
                <strong>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschort')}</strong>: {$smarty.session.wunschlocation}
            </p>
        {/if}
    {/block}
{/if}
