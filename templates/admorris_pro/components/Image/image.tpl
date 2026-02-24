{function name="createSvgBackground"}{strip}
background-size:cover; background-image:url(&quot;data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http%3A//www.w3.org/2000/svg' xmlns%3Axlink='http%3A//www.w3.org/1999/xlink' viewBox='0 0 {$width} {$height}'%3E%3Cfilter id='b' color-interpolation-filters='sRGB'%3E%3CfeGaussianBlur stdDeviation='.5'%3E%3C/feGaussianBlur%3E%3CfeComponentTransfer%3E%3CfeFuncA type='discrete' tableValues='1 1'%3E%3C/feFuncA%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Cimage filter='url(%23b)' x='0' y='0' height='100%25' width='100%25' xlink%3Ahref='{$admImage::base64_encode_image($image)}'%3E%3C/image%3E%3C/svg%3E&quot;);
{/strip}{/function}

{if $useProgressiveLoading && $lazy}
    <div class="mediabox-img-wrapper">
{/if}


{if strpos($params.src->getValue(), 'keinBild.gif') !== false}
<img class="{$params.class->getValue()} img-fluid"
     height="{$height}"
     width="{$width}"
     {if $params.alt->hasValue()}alt="{$params.alt->getValue()}"{else}alt=""{/if}
     src="{$params.src->getValue()}">
{else}
    <img 
        {if $lazy}
            src="{if $width === 'auto'}data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7{else}data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$width} {$height}'%3E%3C/svg%3E{/if}"
        {elseif $params.opc->getValue() === true}
            src={$opcSrcSet[0]->path}
        {else}
            src="{$params.src->getValue()}"
        {/if}

        {if $lazy || $params.opc->getValue() === true}
            {if $params.srcset->hasValue()}
                {if $lazy}data-{/if}srcset="{$params.srcset->getValue()}"
            {elseif $params.opc->getValue() === true}
                {if $lazy}data-{/if}srcset="{$opcSrcSetString}"
            {/if}
            {if $lazy}data-{/if}src="{$params.src->getValue()}"

        {elseif $params.srcset->hasValue()}
            srcset="{$params.srcset->getValue()}"
        {/if}

        {if $params.sizes->hasValue()}{if $params.sizes->getValue() === 'auto'}data-{/if}sizes="{$params.sizes->getValue()}"{/if}
        height="{$height}"
        width="{$width}"
        {if $useProgressiveLoading && $lazy}
            data-lowsrc="{if $params.opc->getValue() === true}{$opcSrcSet[0]->path}{else}{$params.progressiveLoading->getValue()}{/if}"
        {/if}
        {$style = ''}
        {if $usePlaceholder && $params.progressiveLoading->getValue() !== false}
            {* {if $params.opc->getValue() === true && $useWebP}
                {$progressiveSize = imageSize($opcSrcSet[0]->path)}
                {$style = "background-size:cover; background-image:url(&quot;data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http%3A//www.w3.org/2000/svg' xmlns%3Axlink='http%3A//www.w3.org/1999/xlink' viewBox='0 0 {$progressiveSize->width} {$progressiveSize->height}'%3E%3Cfilter id='b' color-interpolation-filters='sRGB'%3E%3CfeGaussianBlur stdDeviation='.5'%3E%3C/feGaussianBlur%3E%3CfeComponentTransfer%3E%3CfeFuncA type='discrete' tableValues='1 1'%3E%3C/feFuncA%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Cimage filter='url(%23b)' x='0' y='0' height='100%25' width='100%25' xlink%3Ahref='{base64_encode_image($opcSrcSet[0]->path)}'%3E%3C/image%3E%3C/svg%3E&quot;)"} *}
            {if $params.opc->getValue()}
                {$style = {createSvgBackground width=$opcSrcSet[0]->width height=$opcSrcSet[0]->height image=$opcSrcSet[0]->path}}
                
                {* "background-size:cover; background-image:url(&quot;data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http%3A//www.w3.org/2000/svg' xmlns%3Axlink='http%3A//www.w3.org/1999/xlink' viewBox='0 0 {$opcSrcSet[0]->width} {$opcSrcSet[0]->height}'%3E%3Cfilter id='b' color-interpolation-filters='sRGB'%3E%3CfeGaussianBlur stdDeviation='.5'%3E%3C/feGaussianBlur%3E%3CfeComponentTransfer%3E%3CfeFuncA type='discrete' tableValues='1 1'%3E%3C/feFuncA%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Cimage filter='url(%23b)' x='0' y='0' height='100%25' width='100%25' xlink%3Ahref='{$admImage::base64_encode_image($opcSrcSet[0]->path)}'%3E%3C/image%3E%3C/svg%3E&quot;);"} *}
            {else}
                {$progressiveSize = $admImage::imageSize($params.progressiveLoading->getValue())}
                {$style = {createSvgBackground width=$progressiveSize->width height=$progressiveSize->height image=$params.progressiveLoading->getValue()}}

                {* {$style = "background-size:cover; background-image:url(&quot;data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http%3A//www.w3.org/2000/svg' xmlns%3Axlink='http%3A//www.w3.org/1999/xlink' viewBox='0 0 {$progressiveSize->width} {$progressiveSize->height}'%3E%3Cfilter id='b' color-interpolation-filters='sRGB'%3E%3CfeGaussianBlur stdDeviation='.5'%3E%3C/feGaussianBlur%3E%3CfeComponentTransfer%3E%3CfeFuncA type='discrete' tableValues='1 1'%3E%3C/feFuncA%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Cimage filter='url(%23b)' x='0' y='0' height='100%25' width='100%25' xlink%3Ahref='{$admImage::base64_encode_image($params.progressiveLoading->getValue())}'%3E%3C/image%3E%3C/svg%3E&quot;);"} *}
            {/if}

        {/if}

        {if $params.style->hasValue()}
            {$styleProp = $params.style->getValue()}
            {$style = $style|cat:$styleProp}
        {/if}

        class="{$params.class->getValue()} {$rounded}{strip}
        {if $usePlaceholder} progressive-src{/if}
        {if $useProgressiveLoading} mediabox-img{/if}
        {if $params.fluid->getValue() === true} img-fluid{/if}
        {if $params['fluid-grow']->getValue() === true} img-fluid{/if}
        {if $params.thumbnail->getValue() === true} img-thumbnail{/if}
        {if $params.left->getValue() === true} float-left{/if}
        {if $params.right->getValue() === true} float-right{/if}
        {if $params.center->getValue() === true} mx-auto d-block{/if}
        {if $params.lazy->getValue() === true} lazy{/if}{/strip}"
        
        {if $params.id->hasValue()}id="{$params.id->getValue()}"{/if}
        {if $params.title->hasValue()}title="{$params.title->getValue()}"{/if}
        {if $params.alt->hasValue()}alt="{$params.alt->getValue()}"{/if}
        {* {if empty($params.lazy->getValue())}
            {if $params.width->hasValue()}width="{$params.width->getValue()}"{/if}
            {if $params.height->hasValue()}height="{$params.height->getValue()}"{/if}
        {/if} *}
        {if !empty($style)}style="{$style}"{/if}
        {if $params.itemprop->hasValue()}itemprop="{$params.itemprop->getValue()}"{/if}
        {if $params.itemscope->getValue() === true}itemscope {/if}
        {if $params.itemtype->hasValue()}itemtype="{$params.itemtype->getValue()}"{/if}
        {if $params.itemid->hasValue()}itemid="{$params.itemid->getValue()}"{/if}
        {if $params.role->hasValue()}role="{$params.role->getValue()}"{/if}
        {if $params.aria->hasValue()}
            {foreach $params.aria->getValue() as $ariaKey => $ariaVal} aria-{$ariaKey}="{$ariaVal}" {/foreach}
        {/if}
        {if $params.data->hasValue()}
            {foreach $params.data->getValue() as $dataKey => $dataVal} data-{$dataKey}="{$dataVal}" {/foreach}
        {/if}
        {if $params.nativeLazyLoading->hasValue()}
            loading="{($params.nativeLazyLoading->getValue()) ? 'lazy' : 'eager'}"
        {/if}
        {if $params.fetchpriority->hasValue()}
            fetchpriority="{$params.fetchpriority->getValue()}"
        {/if}
        
        
        decoding="async"
        
    >

{/if}
{if $useProgressiveLoading  && $lazy}
    </div>
{/if}