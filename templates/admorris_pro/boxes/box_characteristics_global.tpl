{block name='boxes-box-characteristics-global'}
{foreach $oBox->getItems() as $oMerkmal}
    <div class="box box-global-characteristics" id="sidebox{$oBox->getID()}-{$oMerkmal->kMerkmal}">
        <div class="product-filter-headline">
            {if !empty($oMerkmal->cBildpfadKlein) && $oMerkmal->cBildpfadKlein !== $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN}
                <img src="{$oMerkmal->cBildURLKlein}" alt="" class="vmiddle" />
            {/if}
            {$oMerkmal->getName()}
        </div>
        <div class="box-content-wrapper">
            {if ($oMerkmal->getType() === 'SELECTBOX') && $oMerkmal->getCharacteristicValues()|@count > 1}
                <div class="dropdown">
                    <button class="btn btn-secondary btn-block dropdown-toggle" type="button" id="dropdown-characteristics-{$oMerkmal->kMerkmal}" data-toggle="dropdown" aria-expanded="true">
                        {$oMerkmal->getName()}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdown-characteristics-{$oMerkmal->kMerkmal}">
                        {foreach $oMerkmal->getCharacteristicValues() as $oMerkmalWert}
                            <li>
                                <a role="menuitem" tabindex="-1" href="{$oMerkmalWert->cSeo}">
                                    {if ($oMerkmal->getType() === 'BILD' || $oMerkmal->getType() === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                       <img src="{$oMerkmalWert->cBildURLKlein}" alt="{$oMerkmalWert->getValue()|escape:'quotes'}" />
                                    {/if}
                                    {if $oMerkmal->getType() !== 'BILD'}
                                        {$oMerkmalWert->getValue()}
                                    {/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {else}
                <ul class="nav nav-list">
                    {foreach $oMerkmal->getCharacteristicValues() as $oMerkmalWert}
                        <li>
                            <a href="{$oMerkmalWert->getURL()}"{if $NaviFilter->hasCharacteristicValue() && isset($oMerkmalWert->kMerkmalWert) && $NaviFilter->getCharacteristicValue()->getValue() == $oMerkmalWert->kMerkmalWert} class="active"{/if}>
                                {if ($oMerkmal->getType() === 'BILD' || $oMerkmal->getType() === 'BILD-TEXT') && $oMerkmalWert->nBildKleinVorhanden === 1}
                                   <img src="{$oMerkmalWert->cBildURLKlein}" alt="{$oMerkmalWert->getValue()|escape:'quotes'}" />
                                {/if}
                                {if $oMerkmal->getType() !== 'BILD'}
                                    {$oMerkmalWert->getValue()}
                                {/if}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        </div>
    </div>
{/foreach}
{/block}
