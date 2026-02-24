{* admorris pro - custom layout *}
{block name='page-manufacturers'}
    {block name='page-manufacturers-heading'}
        {opcMountPoint id='opc_before_heading' inContainer=false}
        <h1>{lang key='manufacturers'}</h1>
    {/block}
    {opcMountPoint id='opc_before_manufacturers' inContainer=false}
    {block name='page-manufacturers-content'}
        <div class="page-manufacturer css-auto-grid css-auto-grid--fill" >
            {foreach $oHersteller_arr as $mft}
                {link href=$mft->getURL() class='manufacturer stack align-items-center' title=$mft->getMetaTitle()|escape:'html'}
                    {include file='snippets/image.tpl'
                        class="manufacturer__image h-100 object-fit-contain"
                        lazy=true
                        item=$mft
                        alt="{lang section='productOverview' key='manufacturerSingle'}: {$mft->getName()|escape:'html'}"
                        sizes='auto'
                    }
                    <div class="manufacturer__name">{$mft->getName()}</div>
                {/link}
            {/foreach}
        </div>
    {/block}
{/block}
