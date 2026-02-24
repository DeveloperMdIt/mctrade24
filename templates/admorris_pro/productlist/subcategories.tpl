{*custom*}
{block name='productlist-subcategories'}
    {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'N' && $oUnterKategorien_arr|@count > 0}
        {opcMountPoint id='opc_before_subcategories'}
        {* width=$imageData->md->size->width
        height=$imageData->md->size->height *}
        <div class="u-container-inline">
            <div class="css-auto-grid css-auto-grid--fill productlist-subcategories">
                {$admPro->changeCategoriePaths($oUnterKategorien_arr)}
                {foreach $oUnterKategorien_arr as $Unterkat}
                    {$fnAttr = $Unterkat->getFunctionalAttributes()}
                    {if empty($fnAttr.category_hide)}
                        {* <div class="col-6{if !$admPro->is_small_container()} col-lg-4{/if}{if !empty($fnAttr.category_class)} {$fnAttr.category_class}{/if}"> *}
                            <div class="subcategory-card stack{if !empty($fnAttr.category_class)} {$fnAttr.category_class}{/if}">
                                <a class="subcategory-card__link" href="{$Unterkat->getURL()}">
                                    {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'Y'}
                                        {$noImage = ($Unterkat->getImageURL()|strpos:'keinBild.gif' !== false)?' subcategory-card__image--missing':''}
                                        {include file='snippets/image.tpl'
                                            class="subcategory-card__image{$noImage}"
                                            item=$Unterkat
                                            fluid=true
                                            lazy=true
                                        }
                                    {/if}
                                </a>
                                {if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'B'}
                                    <a class="subcategory-card__title" href="{$Unterkat->getURL()}">
                                        {$Unterkat->getName()}
                                    </a>
                                {/if}
                                {if $Einstellungen.navigationsfilter.unterkategorien_beschreibung_anzeigen === 'Y' && !empty($Unterkat->getDescription())}
                                    <div class="subcategory-card__desc">{$Unterkat->getDescription()|strip_tags|truncate:68}</div>
                                {/if}
                                {if $Einstellungen.navigationsfilter.unterkategorien_lvl2_anzeigen === 'Y'}
                                    {if $Unterkat->hasChildren()}
                                        <ul class="subcategory-card__sub-list list-unstyled">
                                            {foreach $Unterkat->getChildren() as $UnterUnterKat}
                                                {$fnAttrSub = $UnterUnterKat->getFunctionalAttributes()}
                                                {if empty($fnAttrSub.category_hide)}
                                                    <li{if !empty($fnAttrSub.category_class)} class="{$fnAttrSub.category_class}"{/if}>
                                                        <a href="{$UnterUnterKat->getURL()}" title="{$UnterUnterKat->getName()}">{$UnterUnterKat->getName()}</a>
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                    {/if}
                                {/if}
                            </div>
                        {* </div> *}
                    {/if}
                {/foreach}
            </div>
        </div>
    {/if}
{/block}