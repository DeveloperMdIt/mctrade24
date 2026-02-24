{strip}
    {* Displays an overlay if the user leaves the document (upwards) *}
    <div class="modal fade" tabindex="-1" role="dialog" id="lpa-behavioral-overlay">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title pull-left">{$lpaBehavioralOverlay.title}</h5>
                    <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{$lpaBehavioralOverlay.text}</p>
                </div>
                <div class="modal-footer">
                    <div id="lpa-behavioral-overlay-button-placeholder" class="text-center"></div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        window.lpaJqAsync = window.lpaJqAsync || [];
        lpaJqAsync.push(['ready', function () {
            $(document).on("mouseleave.lpaBehavioralOverlay", function (e) {
                if (e.pageY - $(window).scrollTop() <= 1) {
                    $('#lpa-behavioral-overlay').modal('show');
                    $(document).off('mouseleave.lpaBehavioralOverlay'); {* Only do this once per page *}
                }
            });
        }]);
    </script>
{/strip}