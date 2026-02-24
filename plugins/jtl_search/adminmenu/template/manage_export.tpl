<div class="jtlsearch_actioncolumn">
    <div class="jtlsearch_inner">
        {if !empty($importStatus)}
            {if $importStatus->_code === 1 || $importStatus->_code === 4 || $importStatus->_code === 5}
                <div id="outputDIV">
                    {if $importStatus->_code === 1}
                        <p class="alert alert-info">{__('infoExportInQueue')}</p>
                    {else}
                        <p class="alert alert-danger">{sprintf(__('errorCodeX'), $importStatus->_code)}</p>
                    {/if}
                </div>
            {else}
                <button type="button" name="start_export" id="start_export" value="1" class="btn btn-success">
                    <i class="fa fa-share"></i> {__('startExport')}
                </button>
                <div id="outputDIV">
                    <p class="alert alert-info">{__('infoStartExport')}</p>
                </div>
            {/if}
        {else}
            <button type="button" name="start_export" id="start_export" value="1" class="btn btn-success">
                {__('startExport')}
            </button>
            <div id="outputDIV">
                <p class="alert alert-info">{__('infoStartExport')}</p>
            </div>
        {/if}
    </div>

</div>
<div class="jtlsearch_infocolumn">
    <div class="jtlsearch_inner">
        <table class="table">
            <tr>
                <th>{__('lastUpdates')}</th>
                <th>{__('updateMethod')}</th>
                <th>{__('timeNeeded')}</th>
            </tr>
            {if isset($importHistory->oImportHistories) && is_array($importHistory->oImportHistories) && $importHistory->_code === 1}
                {foreach $importHistory->oImportHistories as $history}
                    <tr>
                        <td class="tcenter">{$history->dCreated|date_format:'d.m.Y H:i:s'}</td>
                        <td class="tcenter">{if (int)$history->nType === 1} {__('importTypeComplete')}{else}{__('importTypeDelta')}{/if}</td>
                        <td class="tcenter">{$history->nTimeNeeded} {__('seconds')}</td>
                    </tr>
                {/foreach}
            {elseif isset($importHistory->_code ) && $importHistory->_code === 2}
                <tr>
                    <td colspan="3">
                        <div class="alert alert-info">{__('infoNoImports')}</div>
                    </td>
                </tr>
            {/if}
        </table>
    </div>
</div>
<div class="jtlsearch_clear"></div>

<script type="text/javascript">
    var time = new Date();
    $(function () {ldelim}
        $('.datepicker').datetimepicker($.datepicker.regional['de']);
        {rdelim});

    $('#start_export').click(function () {ldelim}
        $('#start_export').hide();
        $('#outputDIV').html('<div class="alert alert-info">{__('exporting')}</div>');

        $.ajax({ldelim}
            url:     "{$shopURL}/?fromAdmin=yes&jtlsearchsetqueue=2&v=" + time.getTime(),
            success: function (cRes) {ldelim}
                if (cRes == 1) {ldelim}
                    sendExportRequest();
                {rdelim}
            {rdelim},
            error:   function () {ldelim}
                $('#outputDIV').html('{__('errorExporting')}');
                $('#start_export').show();
                {rdelim},
            timeout: 15000
            {rdelim});
        {rdelim});

    function sendExportRequest() {ldelim}
        var time = new Date();
        $.ajax({ldelim}
            url:     "{$shopURL}/?jtlsearch=true&nExportMethod=2&v=" + time.getTime(),
            success: function (cRes) {ldelim}
                let oRes = jQuery.parseJSON(cRes),
                    $outputContainer = $('#outputDIV')
                    responseMsg = '';
                if (oRes.nReturnCode == 1) {ldelim}
                    $outputContainer.html(oRes.nExported + " von " + oRes.nCountAll + " Items exportiert.<br />");
                    $outputContainer.append('<div style="border: 1px solid #000000; margin: 10px auto; width: 230px; height: 20px;"><div style="background-color: #FF0000; height: 100%; width:' + (100 / oRes.nCountAll * oRes.nExported) + '%;"></div></div>');
                    sendExportRequest();
                    {rdelim} else {ldelim}
                        $outputContainer.html('<div class="alert alert-info">' + oRes.nExported + " von " + oRes.nCountAll + " Items exportiert.</div>");

                        //Antwort-/Fehler-Codes:
                        // 1 = Alles O.K.
                        // 2 = Authentifikation fehlgeschlagen
                        // 3 = Benutzer wurde nicht gefunden
                        // 4 = Auftrag konnte nicht in die Queue gespeichert werden
                        // 5 = Requester IP stimmt nicht mit der Domain aus der Datenbank ueberein
                        // 6 = Der Shop wurde bereits zum Importieren markiert
                        // 7 = Exception
                        // 8 = Zeitintervall von Full Import zu gering
                        switch (parseInt(oRes.nServerResponse)) {ldelim}
                            case 1:
                            case 6:
                                $outputContainer.append('<div class="alert alert-info">{__('successWriteExportInQueue')}</div>');
                                break;
                            case 2:
                                responseMsg = '{__('error2AuthenticationFail')}';
                                break;
                            case 3:
                                responseMsg = '{__('error3TrialExpired')}';
                                break;
                            case 4:
                                responseMsg = '{__('error4WriteInQueue')}';
                                break;
                            case 5:
                                responseMsg = '{__('error5RequestIP')}';
                                break;
                            case 7:
                                responseMsg = '{__('error7UnknownServer')}';
                                break;
                            case 8:
                                responseMsg = '{__('error8LimitReached')}';
                                break;
                            case 0:
                                responseMsg = '{__('errorNoData')}';
                                break;
                            default:
                                responseMsg = '{__('errorUnknownServer')}';
                                break;
                        {rdelim}
                        if (responseMsg !== '') {ldelim}
                            $outputContainer.append('<div class="alert alert-danger">' + responseMsg + '</div>');
                        {rdelim}
                    {rdelim}
                {rdelim},
            error:   function () {ldelim}
                $('#outputDIV').html('<div class="alert alert-danger">{__('errorDuringExport')}</div>');
                $('#start_export').show();
                {rdelim}
            {rdelim});
        {rdelim}
</script>
