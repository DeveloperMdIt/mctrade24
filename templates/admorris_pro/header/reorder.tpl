
{if !empty($smarty.session.Kunde->kKunde)}
    {if !isset($Bestellungen)}
        {$orders = $admPro->getCustomerOrders()}
    {else}
        {$orders = $Bestellungen}
    {/if}

    {if $layoutType == 'mobileLayout'}
        {$reorderId = 'reorder-mobile-menu-button'}
    {elseif $layoutType == 'offcanvasLayout'}
        {$reorderId = 'reorder-offcanvas-menu-button'}
    {else}
        {$reorderId = 'reorder-desktop-menu-button'}
    {/if}

    {* {var_dump($orders)} *}

    {if  !empty($orders)}
        {block 'header-shopnav-reorder'}
            <div id="{$reorderId}"></div>
        {/block}
    {/if}
    
{/if}