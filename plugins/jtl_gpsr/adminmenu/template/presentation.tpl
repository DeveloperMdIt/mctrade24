<form name="presentations" method="post" action="{$adminURL}{$route}" enctype="multipart/form-data">
    {$jtl_token}
    <input type="hidden" name="kPlugin" value="{$kPlugin}">
    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}">
    <div class="subheading1">
        <label class="" for="template_manufacturer">{__('Darstellung des Herstelllers')}:</label>
        <hr />
    </div>
    <textarea class="codemirrorHidden smarty" id="template_manufacturer" name="template_manufacturer" rows="20">{$gpsr_presentation.manufacturer}</textarea>
    <div class="subheading1 mt-5">
        <label class="" for="template_responsibleperson">{__('Darstellung der verantwortlichen Person')}:</label>
        <hr />
    </div>
    <textarea class="codemirrorHidden smarty" id="template_responsibleperson" name="template_responsibleperson" rows="20">{$gpsr_presentation.responsibleperson}</textarea>
    <div class="save-wrapper">
        <div class="row">
            <div class="ml-auto col-sm-6 col-xl-auto">
                <button type="submit" name="task" value="resetPresentation" class="btn btn-danger btn-block btn-reset-all">
                    <i class="fas fa-refresh"></i> {__('reset')}
                </button>
            </div>
            <div class="col-sm-6 col-xl-auto">
                <button type="submit" name="task" value="savePresentation" class="btn btn-primary btn-block">
                    {__('saveWithIcon')}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    (function() {
        let editorSmarty = null;

        function initCodemirror() {
            if (editorSmarty === null) {
                editorSmarty = [];
                (['template_manufacturer', 'template_responsibleperson']).forEach(function (elem, idx) {
                    editorSmarty[idx] = CodeMirror.fromTextArea(document.getElementById(elem), {
                        lineNumbers: true,
                        lineWrapping: true,
                        mode: 'smartymixed',
                        scrollbarStyle: 'simple',
                        extraKeys: {
                            'Ctrl-Space': function (cm) {
                                cm.setOption('fullScreen', !cm.getOption('fullScreen'));
                            },
                            'Esc': function (cm) {
                                if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
                            }
                        }
                    });
                });
            }
        }

        $(window).on('load', function() {
            let $tab  = $('.tab-link-{$presentationTabId}[data-toggle="tab"]'),
                $form = $('#plugin-tab-{$presentationTabId} form');
            if ($tab.hasClass('active')) {
                initCodemirror();
            } else {
                $('.tab-link-{$presentationTabId}[data-toggle="tab"]').on('shown.bs.tab', function () {
                    initCodemirror();
                });
            }

            $form.on('submit', function (e) {
                if ($(e.originalEvent.submitter).val() === 'resetPresentation') {
                    return (window.confirm('{__('Wollen Sie die Darstellung der Herstellerinformationen zurücksetzen?')}'));
                }

                return true;
            });
        });
    })();
</script>