<div class="col-md-12">
    {if !isset($fs_it_recht_kanzlei_log_stepAction) || $lfs_it_recht_kanzlei_log_stepAction != "delete"}
        <b>{__("Anzahl Datensätze")}:</b> {$lfs_it_recht_kanzlei_log_cAnzahl}

        <table class="table table-bordered">
            <thead>
            <tr align="center">
                <th>{__("Nr")}</th>
                <th>{__("Datum")}</th>
                <th>{__("Dokumentenart")}</th>
                <th>{__("Status")}</u></th>
                <th>{__("Sprache")}</th>
                <th>{__("PDF-Datei")}</th>
            </tr>
            </thead>
            <tbody>
            {foreach name="logdatei" from=$lfs_it_recht_kanzlei_log_arr item=lfs_it_recht_kanzlei_log}
                {assign var=i value=0}
                <tr class="tab_bg{$smarty.foreach.httpbl.iteration%2}">
                    <td class="tcenter">{$lfs_it_recht_kanzlei_log->kUpdate}</td>
                    <td class="tcenter">{$lfs_it_recht_kanzlei_log->dLetzterPush|date_format:"%d.%m.%Y, %H:%M:%S"}</td>
                    <td class="tcenter">{$lfs_it_recht_kanzlei_log->cDokuArt}</td>
                    <td class="tcenter">{$lfs_it_recht_kanzlei_log->cStatus}</td>
                    <td class="tcenter">{$lfs_it_recht_kanzlei_log->cSprache}</td>
                    <td class="tcenter">{if $lfs_it_recht_kanzlei_log->cPDFName != "-"}<a href="{$URL_SHOP}/plugins/lfs_it_recht_kanzlei/pdf-dokumente/{$lfs_it_recht_kanzlei_log->cPDFName}" target="_blank">{$lfs_it_recht_kanzlei_log->cPDFName}</a>{else}{$lfs_it_recht_kanzlei_log->cPDFName}{/if}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
    <div class="btn-group">
        <button class="btn btn-primary" onClick="location.href='{$adminUrl}{if $adminSeo}?{else}&{/if}action=refresh&cPluginTab=Rechtstexte-Log'" type="submit" name="btnRefreshLog"><i class="fa fa-refresh"></i> {#update#}</button>
        {if $lfs_it_recht_kanzlei_log_cAnzahl != 0 && (!isset($lfs_it_recht_kanzlei_log_stepAction) || $lfs_it_recht_kanzlei_log_stepAction != "delete")}
            <button class="btn btn-danger" onClick="location.href='{$adminUrl}{if $adminSeo}?{else}&{/if}action=delete&cPluginTab=Rechtstexte-Log'" type="submit" name="btnDeleteLog"><i class="fa fa-trash"></i> {__("Einträge löschen")}</button>
        {/if}
    </div>
</div>
