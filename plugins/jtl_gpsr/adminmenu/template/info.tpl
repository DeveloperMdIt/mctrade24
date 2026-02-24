<div class="subheading1">{__('JTL GPSR - Angaben zur Produktsicherheit')}</div>
<hr>
<p>{__('Beschreibung der Angaben zur Produktsicherheit')}</p>
<form name="presentations" method="post" action="{$adminURL}{$route}" enctype="multipart/form-data">
    {$jtl_token}
    <input type="hidden" name="kPlugin" value="{$kPlugin}">
    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}">
    <div class="save-wrapper">
        <div class="row">
            <div class="ml-auto col-sm-6 col-xl-auto">
                <button type="submit" name="task" value="resetAllSettings" class="btn btn-danger btn-block btn-reset-all">
                    <i class="fas fa-refresh"></i> {__('reset')}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    function swapOptions($select, options) {
        $select.children('option').remove();
        $.each(options, function(offset, optionObject) {
            if (optionObject.hasOwnProperty('cWert') && optionObject.hasOwnProperty('cName')) {
                $select.append($("<option></option>")
                    .attr('value', optionObject.cWert)
                    .text(optionObject.cName));
            }
        });
    }

    $(window).on('load', function() {
        $('#gpsr_templatefile_productdetails').on('change', function () {
            ioCall(
                'getTemplateBlocks', ['{$detailConstant}', $(this).val()],
                function (templateBlocks) {
                    swapOptions($('#gpsr_templateblock_productdetails'), templateBlocks);
                }
            );
        });
        $('#gpsr_templatefile_productlist').on('change', function () {
            ioCall(
                'getTemplateBlocks', ['{$listConstant}', $(this).val()],
                function (templateBlocks) {
                    swapOptions($('#gpsr_templateblock_productlist'), templateBlocks);
                }
            );
        });
        $('#plugin-tab-{$infoTabId} form').on('submit', function () {
            return (window.confirm('{__('Wollen Sie alle Einstellungen auf Standardwerte zurücksetzen?')}'));
        });
    });
</script>
