{block name='snippets-overlay'}
    {if $Einstellungen.template.productlist.overlays !== 'image'}
        {if isset($Artikel->oSuchspecialBild) && !isset($hideOverlays)}
            {if $Artikel->oSuchspecialBild->getType() === $smarty.const.SEARCHSPECIALS_CUSTOMBADGE}
                {assign var=customBadge value=$Artikel->oSuchspecialBild->getCssAndText()}
                <div class="overlay-label {if $customBadge->class !== ''}{$customBadge->class}{/if}"
                    {if $customBadge->style !== ''} style="{$customBadge->style}"{/if}>
                    {block name='snippets-custom-overlay-content'}
                        {$customBadge->text}
                    {/block}
                </div>
            {else}
                {assign var="overlayCSS" value=""}
                {if isset($Artikel->oSuchspecialBild->getName())}
                    {assign var="cSuchspecial" value=$Artikel->oSuchspecialBild->getName()|to_charset:"UTF-8"|regex_replace:'/ü/':'u'|lower|strip:''}
                    {lang assign="cSuchspecialLang" section="custom" key=$cSuchspecial}
                    {$overlayCSS=' '|cat:$cSuchspecial}
                {else}
                    {assign var="cSuchspecialLang" value=$Artikel->cName}
                {/if}
                <span class="overlay-label{$overlayCSS}">{$cSuchspecialLang}</span>
            {/if}
        {/if}
    {else}
        {if isset($Artikel->oSuchspecialBild) && !isset($hideOverlays) && $Artikel->oSuchspecialBild->getType() !== $smarty.const.SEARCHSPECIALS_CUSTOMBADGE}
            <img class="overlay-img" srcset="{$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_XS)},
                 {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_SM)} 2x,
                 {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_MD)} 3x,
                 {$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_LG)} 4x"
                src="{$Artikel->oSuchspecialBild->getURL($smarty.const.IMAGE_SIZE_XS)}"
                alt="{if isset($Artikel->oSuchspecialBild->getName())}{$Artikel->oSuchspecialBild->getName()}{else}{$alt}{/if}" />
        {/if}
    {/if}
{/block}