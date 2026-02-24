<div class="settings-content">
    <form method="post" action="{$adminUrl}" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$plugin->getID()}">
        <div class="panel-idx-0 first mb-3">
            <div class="subheading1">Allgemein
            </div>
            <hr>
            <div class="">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right"
                           for="sv_show_productreview">{__('ShopVote-Produktbewertungen anzeigen')}</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="sv_show_productreview" name="sv_show_productreview" class="custom-select">
                            <option value="R"{if $sv_show_productreview == 'R'} selected{/if}>{__('Shopeigene Bewertungen ersetzen')}</option>
                            <option value="A"{if $sv_show_productreview == 'A'} selected{/if}>{__('Shopeigene Bewertungen und ShopVote Bewertungen anzeigen')}</option>
                            <option value="N"{if $sv_show_productreview == 'N'} selected{/if}>{__('Nein')}</option>
                        </select>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        <span data-html="true" data-toggle="tooltip"
                              data-placement="left" title=""
                              data-original-title="{__('Die Bewertungen können mit den shopeigenen Bewertungen zusammengefasst werden oder die shopeigenen Bewertungen werden durch die ShopVote Bewertungen ersetzt')}">
                            <span class="fas fa-info-circle fa-fw"></span>
                        </span>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="sv_api_key">
                        {__('API-Key')}
                    </label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="sv_api_key" name="sv_api_key" type="text" value="{$sv_api_key}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="sv_api_secret">
                        {__('API-Secret')}
                    </label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="sv_api_secret" name="sv_api_secret" type="text" value="{$sv_api_secret}">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="sv_graphic_code">
                        {__('Grafik-Code')}
                    </label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <textarea id="sv_graphic_code" name="sv_graphic_code" class="form-control" style="min-height: 150px;">{$sv_graphic_code}</textarea>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        <span data-html="true" data-toggle="tooltip"
                              data-placement="left" title=""
                              data-original-title="{__('Hinterlegen Sie in diesem Feld den Grafik-Code den Sie im Backend von ShopVote beziehen können')}">
                            <span class="fas fa-info-circle fa-fw"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="subheading1">Consentmanager-Integration
            </div>
            <hr>

            <div class="">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right"
                           for="sv_respect_jtlconsentmanager">{__('Shop5-Consentmanager beachten?')}</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select id="sv_respect_jtlconsentmanager" name="sv_respect_jtlconsentmanager" class="custom-select">
                            <option value="0"{if $sv_respect_jtlconsentmanager == '0'} selected{/if}>{__('Nein')}</option>
                            <option value="1"{if $sv_respect_jtlconsentmanager == '1'} selected{/if}>{__('Ja')}</option>
                        </select>
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        <span data-html="true" data-toggle="tooltip"
                              data-placement="left" title=""
                              data-original-title="{__('Soll eine Zustimmung über den JTL-Shop5-Consentmanager beachtet werden?')}">
                            <span class="fas fa-info-circle fa-fw"></span>
                        </span>
                    </div>
                </div>

            </div>


            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button name="speichern" type="submit" value="Speichern" class="btn btn-primary btn-block">
                            <i class="fal fa-save"></i> Speichern
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>