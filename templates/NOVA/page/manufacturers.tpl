{block name='page-manufacturers'}
    {block name='page-manufacturers-heading'}
        {opcMountPoint id='opc_before_heading' inContainer=false}
        {container fluid=$Link->getIsFluid() class="{if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
            <h1>{lang key='manufacturers'}</h1>
        {/container}
    {/block}
    {opcMountPoint id='opc_before_manufacturers' inContainer=false}
    {block name='page-manufacturers-content'}
        {container fluid=$Link->getIsFluid() class="page-manufacturers {if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
            {row}
                {foreach $oHersteller_arr as $mft}
                    {col xl=3 md=4 cols=6}
                        {link href=$mft->getURL() title=$mft->getMetaTitle()|escape:'html'}
                            {include file='snippets/image.tpl'
                                lazy=($mft@iteration > 8)
                                item=$mft
                                sizes = '(min-width: 1300px) 25vw, (min-width: 992px) 34vw, 50vw'
                                alt="{lang section='productOverview' key='manufacturerSingle'}: {$mft->getName()|escape:'html'}"
                            }
                            {$mft->getName()}
                        {/link}
                    {/col}
                {/foreach}
            {/row}
        {/container}
    {/block}
{/block}
