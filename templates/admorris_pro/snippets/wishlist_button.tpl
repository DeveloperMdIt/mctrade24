{* modified template from NOVA *}
{block name='snippets-wishlist-button'}
    {assign var='isOnWishList' value=false}
    {assign var='wishlistPos' value=0}
    {assign var='isVariationItem' value=!empty($Artikel->Variationen) && empty($Artikel->kVariKindArtikel)}
    {if isset($smarty.session.Wunschliste) && !$isVariationItem}
        {foreach $smarty.session.Wunschliste->CWunschlistePos_arr as $product}
            {if $product->kArtikel === $Artikel->kArtikel || $product->kArtikel === $Artikel->kVariKindArtikel}
                {$isOnWishList=true}
                {$wishlistPos=$product->kWunschlistePos}
                {break}
            {/if}
        {/foreach}
    {/if}
    {block name='snippets-wishlist-button-main'}
        {if $buttonAndText|default:false}
            {block name='snippets-wishlist-button-button-text'}
                {button
                    name="Wunschliste"
                    type="submit"
                    variant={$variant|default:'link'}
                    class="{$classes|default:''} wishlist-button wishlist action-tip-animation-b{if $isOnWishList} on-list{/if}"
                    aria=["label" => {lang key='addToWishlist' section='productDetails'}]
                    data=["wl-pos" => $wishlistPos, "product-id-wl" => "{if isset($Artikel->kVariKindArtikel)}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}"]}
                    <span class="wishlist-button-inner">
                        {if $isOnWishList}
                            {$admIcon->renderIcon('heart', 'icon-content icon-content--default wishlist-icon is-on-wishlist')}
                        {else}
                            {$admIcon->renderIcon('heart', 'icon-content icon-content--default wishlist-icon')}
                        {/if}
                        <span class="wishlist-button-text">{lang key='onWishlist'}</span>
                    </span>
                {/button}
            {/block}
        {else}
            {block name='snippets-wishlist-button-button'}
                {button
                    name="Wunschliste"
                    type="submit"
                    variant={$variant|default:'secondary'}
                    class="{$classes|default:''} wishlist-button wishlist{if $isOnWishList} on-list{/if}"
                    aria=["label" => {lang key='addToWishlist' section='productDetails'}]
                    title={lang key='addToWishlist' section='productDetails'}
                    data=[
                        "wl-pos" => $wishlistPos,
                        "product-id-wl" => "{if isset($Artikel->kVariKindArtikel)}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}",
                        "toggle"=>"tooltip",
                        "trigger"=>"hover"
                    ]}
                   {$admIcon->renderIcon('heart', 'icon-content icon-content--default icon-content--center')}
                {/button}
            {/block}
        {/if}
        {block name='snippets-wishlist-button-hidden'}
            {input type="hidden" name="wlPos" value=$wishlistPos}
        {/block}
    {/block}
{/block}