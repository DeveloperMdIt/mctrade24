<form method="post" enctype="multipart/form-data" name="export">
    {$jtl_token}
    <input class="form-control" type="hidden" name="kPlugin" value="{$oPlugin->getID()}"/>
    <input class="form-control" type="hidden" name="cPluginTab" value="Einstellungen"/>
    <input class="form-control" type="hidden" name="stepPlugin" value="{$stepPlugin}"/>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <td colspan="2">
                    <b>{__('Systemumgebung')}</b>
                </td>
            </tr>
            <tr>
                <td>{__('Shop-URL')}</td>
                <td>{$cAPIUrl}</td>
            </tr>
            <tr>
                <td>{__("API-TOKEN")}</td>
                <td><input class="form-control" readonly="readonly" style="width: 300px;" type="text" name="cAPIToken"
                           value="{$cEinstellungen_arr.cAPIToken}"/></td>
            </tr>
            <tr>
                <td>{__("PDF-Verzeichnis-Historie")}</td>
                <td>{$cPDFDir}</td>
            </tr>
            <tr>
                <td>{__("PDF-Verzeichnis-Historie-Status")}</td>
                <td>{if $bPDFDirStatus===true}
                        <span style="color: green;">{__("beschreibbar")}</span>
                    {else}
                        <span style="color: red;">{__("Fehler: nicht beschreibbar (bitte Verzeichnisrechte anpassen)")}</span>
                    {/if}</td>
            </tr>
            <tr>
                <td>{__("PDF-Verzeichnis-Mailanhang")}</td>
                <td>{$cPDFDir_Mail}</td>
            </tr>
            <tr>
                <td>{__("PDF-Verzeichnis-Mailanhang-Status")}</td>
                <td>{if $bPDFDir_Mail_Status===true}
                        <span style="color: green;">{__("beschreibbar")}</span>
                    {else}
                        <span style="color: red;">{__("Fehler: nicht beschreibbar (bitte Verzeichnisrechte anpassen)")}</span>
                    {/if}</td>
            </tr>
            <tr>
                <td>{__("Spezialseiten-Status")}</td>
                <td>
                    {if $checkStatus_agb=="1"}<span style="color: green;">{__("AGB ok")}</span>{else}<span style="color: red;">{__("AGB existiert nicht")}</span>{/if}
                    /
                    {if $checkStatus_widerruf===1}
                        <span style="color: green;">{__("WRB ok")}</span>
                    {else}
                        <span style="color: red;">{__("WRB existiert nicht")}</span>
                    {/if} /
                    {if $checkStatus_impressum===1}
                        <span style="color: green;">{__("Impressum ok")}</span>
                    {else}
                        <span style="color: red;">{__("Impressum existiert nicht")}</span>
                    {/if} /
                    {if $checkStatus_datenschutz===1}
                        <span style="color: green;">{__("Datenschutz ok")}</span>
                    {else}
                        <span style="color: red;">{__("Datenschutz existiert nicht")}</span>
                    {/if}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #ffffff;"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>{__("Einstellungsparameter")}</b>
                </td>
            </tr>
            <tr>
                <td>{__("PDF-Dokumente downloaden")}</td>
                <td>
                    <select class="form-control" name="cPDFDown">
                        <option value="1" {if $cEinstellungen_arr.cPDFDown=="1"}selected{/if}>{__("Ja")}</option>
                        <option value="0" {if $cEinstellungen_arr.cPDFDown=="0"}selected{/if}>{__("Nein")}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>{__('Rechtstexte an Bestellbestätigungs-Mail anhängen')}</td>
                <td>
                    <select class="form-control" name="cPDFMail">
                        <option value="1" {if $cEinstellungen_arr.cPDFMail=="1"}selected{/if}>{__("Ja, als PDF-Datei")}</option>
                        <option value="2" {if $cEinstellungen_arr.cPDFMail=="2"}selected{/if}>{__("Ja, als Mailtext")}</option>
                        <option value="3" {if $cEinstellungen_arr.cPDFMail=="3"}selected{/if}>{__("Ja, als PDF-Datei & Mailtext")}</option>
                        <option value="0" {if $cEinstellungen_arr.cPDFMail=="0"}selected{/if}>{__("Nein")}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>{__('Anzuhängende Rechtstexte (falls aktiviert!)')}</td>
                <td>
                    <table class="table">
                        <tr>
                            <td>
                                {__('Allgemeine Geschäftsbedingungen')}
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="activeAttachments[]" value="agb"
                                            {if is_array($cEinstellungen_arr.activeAttachments) && in_array('agb', $cEinstellungen_arr.activeAttachments)} checked{/if}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {__('Widerrufsbelehrung')}
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="activeAttachments[]" value="wrb"
                                            {if is_array($cEinstellungen_arr.activeAttachments) && in_array('wrb', $cEinstellungen_arr.activeAttachments)} checked{/if}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {__('Datenschutzerklärung')}
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="activeAttachments[]" value="dse"
                                            {if is_array($cEinstellungen_arr.activeAttachments) && in_array('dse', $cEinstellungen_arr.activeAttachments)} checked{/if}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
            <tr>
                <td>{__("Datenschutztext speichern unter")}</td>
                <td>
                    <select class="form-control" name="saveDseContentAs">
                        <option value="content" {if $cEinstellungen_arr.saveDseContentAs=="content"}selected{/if}>{__("Inhaltsseite mit Spezialtyp Datenschutz")}</option>
                        <option value="legaltext" {if $cEinstellungen_arr.saveDseContentAs=="legaltext"}selected{/if}>{__("Rechtstext-Element im Bereich der AGB/WRB")}</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <button type="submit" class="btn btn-primary" value="Einstellungen speichern">
        <i class="fa fa-save"></i> {__("Einstellungen speichern")}
    </button>
</form>