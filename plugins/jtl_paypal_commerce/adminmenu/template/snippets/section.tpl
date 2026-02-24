<div class="card card-gray">
    <div class="card-body">
        {foreach $section['settings'] as $settingsName => $setting}
            {if isset($setting['wrapperStart'])}
                {$setting['wrapperStart']}
            {/if}
            {if $setting['type']=== 'text'}
                {include file="$basePath/adminmenu/template/snippets/textInput.tpl" }
            {/if}
            {if $setting['type']=== 'hidden'}
                {include file="$basePath/adminmenu/template/snippets/hiddenInput.tpl" }
            {/if}
            {if $setting['type']=== 'password'}
                {include file="$basePath/adminmenu/template/snippets/password.tpl" }
            {/if}
            {if $setting['type']=== 'partial_readonly'}
                {include file="$basePath/adminmenu/template/snippets/partial_readonly.tpl" }
            {/if}
            {if $setting['type'] === 'selectbox'}
                {include file="$basePath/adminmenu/template/snippets/selectbox.tpl" }
            {/if}
            {if $setting['type'] === 'activationList'}
                {include file="$basePath/adminmenu/template/snippets/activationList.tpl" }
            {/if}
            {if $setting['type'] === 'info'}
                {include file="$basePath/adminmenu/template/snippets/info.tpl" }
            {/if}
            {if isset($setting['wrapperEnd'])}
                {$setting['wrapperEnd']}
            {/if}
            {if isset($setting['loadAfter']) && !is_array($setting['loadAfter'])}

                {assign var="customComponent" value=$setting['loadAfter']}
                {include file="$basePath/adminmenu/template/$customComponent" }

            {elseif isset($setting['loadAfter']) && is_array($setting['loadAfter'])}
                {foreach $setting['loadAfter'] as $loadAfter}
                    {assign var="customComponent" value=$loadAfter}
                    {include file="$basePath/adminmenu/template/$customComponent" }
                {/foreach}
            {/if}
        {/foreach}
    </div>
</div>