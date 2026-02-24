<script>
    (function() {
        let settingSection               = '{$setting['vars']['section']}';
        let activatorClass               = '.singleActivator_' + settingSection;
        let advancedSettingsClass        = '.advancedSettings_' + settingSection;
        let advancedSettingsWrapperClass = '.advancedSettingsWrapper_' + settingSection;
        {literal}
        $(document).ready(function() {
            $(activatorClass + ' select.custom-select').each(function(key,item) {
                let parent = $(item).closest(activatorClass);
                let advancedSettingsButton = parent.nextAll(advancedSettingsClass).first();
                let advancedSettings = advancedSettingsButton.nextAll(advancedSettingsWrapperClass).first();
                if ($(item).val() === 'Y') {
                    advancedSettingsButton.show();
                } else {
                    advancedSettingsButton.hide();
                    advancedSettings.hide();
                }
            }).on('change',function() {
                let parent = $(this).closest(activatorClass);
                let advancedSettingsButton = parent.nextAll(advancedSettingsClass).first();
                let advancedSettings = advancedSettingsButton.nextAll(advancedSettingsWrapperClass).first();
                if ($(this).val() === 'Y') {
                    advancedSettingsButton.show();
                    advancedSettingsButton.find('i.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    advancedSettingsButton.hide();
                    advancedSettingsButton.find('i.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    advancedSettings.hide();

                }
            })

            let globalSelect = $('#setting_' + settingSection + '_activate');
            let advancedSettingsButtons = $(advancedSettingsClass);
            let advancedSettings = $(advancedSettingsWrapperClass);

            if (globalSelect.val() === 'N') {
                advancedSettingsButtons.hide();
                advancedSettings.hide();
            }

            globalSelect.on('change',function() {
                if ($(this).val() === 'N') {
                    advancedSettingsButtons.hide();
                    advancedSettingsButtons.find('i.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    advancedSettings.hide();
                } else {
                    $(activatorClass + ' select.custom-select').trigger('change');
                }
            })

            $(advancedSettingsClass + ' button.advancedSettingsButton').on('click',function () {
                $(this).closest(advancedSettingsClass)
                    .nextAll(advancedSettingsWrapperClass).first().toggle();
                if ($(this).find('i.fa').hasClass('fa-chevron-up') === true) {
                    $(this).find('i.fa').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    $(this).find('i.fa').removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            })
            $('[id^=setting_instalmentBannerDisplay_][id$=_layout]').each(function(key,item) {
                let mainID = '#' + $(this).attr('id').replace('setting_','component_').replace('_layout','_');

                if ($(this).val() === 'flex') {
                    $(mainID + 'layoutRatio').show();
                    $(mainID + 'layoutType').show();
                    $(mainID + 'textSize').hide();
                    $(mainID + 'textColor').hide();
                    $(mainID + 'logoType').hide();
                } else {
                    $(mainID + 'layoutRatio').hide();
                    $(mainID + 'layoutType').hide();
                    $(mainID + 'textSize').show();
                    $(mainID + 'textColor').show();
                    $(mainID + 'logoType').show();
                }
            }).on('change',function() {
                let mainID = '#' + $(this).attr('id').replace('setting_','component_').replace('_layout','_');
                if ($(this).val() === 'flex') {
                    $(mainID + 'layoutRatio').show();
                    $(mainID + 'layoutType').show();
                    $(mainID + 'textSize').hide();
                    $(mainID + 'textColor').hide();
                    $(mainID + 'logoType').hide();
                } else {
                    $(mainID + 'layoutRatio').hide();
                    $(mainID + 'layoutType').hide();
                    $(mainID + 'textSize').show();
                    $(mainID + 'textColor').show();
                    $(mainID + 'logoType').show();
                }
            });
        })
        {/literal}
    })()
</script>
