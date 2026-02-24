<style>
    .card-gray{
        box-shadow: none;
    }
    .tab-grey{
        box-shadow: none;
    }
    .card-border{
        border: 1px solid #e4e9f2;
        border-radius: 0.25em;
    }
</style>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    {if $panelActive >= $settingsPanels|@count}{$panelActive = 0}{/if}
    <script>let panelButtons = [];</script>
    {foreach $settingsPanels as $panelName => $panel}
        <li class="nav-item">
            <a class="nav-link {if $panel@index === $panelActive} active {/if}"
               id="{$panelName}-tab"
               data-toggle="pill"
               href="#{$panelName}"
               role="tab"
               aria-controls="{$panelName}"
               aria-selected="{if $panel@index === 0}true{else}false{/if}">
                {__($panelName)}
            </a>
        </li>
        <script>panelButtons[{$panel@index}] = "{$panelName}";</script>
    {/foreach}
    <script>
        {literal}
        panelButtons.forEach(function(panelName, panelIndex, arr) {
            $("#"+panelName+"-tab").on("click", function() {
                $("input[name=panelActive]").val(panelIndex);
            });
        });
        {/literal}
    </script>
</ul>
<div class="card card-gray mb-0">
    <div class="card-body card-border">
        <div class="tab-content tab-grey" id="pills-tabContent">
            {foreach $settingsPanels as $panelName => $panel}
            <div class="tab-pane fade {if $panel@index === $panelActive}show active{/if}" id="{$panelName}" role="tabpanel" aria-labelledby="{$panelName}-tab">
                {foreach $panel as $sectionName => $section}
                    <span class="subheading1">{$section['heading']}</span>
                    {assign var="sectionDescription" value=$configuration->getSectionDescription($sectionName)}
                    {if isset($section['settings'])}
                    {assign var="sectionDescriptionType" value=$configuration->getSectionDescriptionType($section['settings'])}
                    {else}
                    {assign var="sectionDescriptionType" value=""}
                    {/if}
                    {if $sectionDescription !== ''}
                        <p class="{if $sectionDescriptionType !== ''}alert alert-{$sectionDescriptionType}{/if}">
                            {$sectionDescription}
                        </p>
                    {/if}
                    <hr class="mb-3">
                    {if isset($section['settings'])}
                        {include file="$basePath/adminmenu/template/snippets/section.tpl" }
                    {/if}
                {/foreach}
            </div>
            {/foreach}
        </div>
    </div>
</div>
<div class="save-wrapper mt-0">
    <div class="row">
        <div class="mr-auto col-sm-6 offset col-xl-auto">
            <button id="resetSettings" type="submit" name="task" value="resetSettings" class="btn btn-info btn-block">
                <i class="far fa-undo mr-0 mr-lg-2"></i>{__('Einstellungen zurücksetzen')}
            </button>
        </div>
        <div class="ml-auto col-sm-6 col-xl-auto">
            <button type="submit" class="btn btn-primary btn-block" name="task" value="saveSettings">
                <i class="fal fa-save mr-0 mr-lg-2"></i> {__('Save')}
            </button>
        </div>
    </div>
</div>
<script>
    $('#resetSettings').click(function(e) {
        e.preventDefault();
        let answer = confirm(
            '{addslashes(__('Wollen Sie die Einstellungen auf den Ausgangszustand zurücksetzen?'))}'
        );
        if (answer === true) {
            $(this).unbind('click').trigger('click');
        }
    })
</script>
