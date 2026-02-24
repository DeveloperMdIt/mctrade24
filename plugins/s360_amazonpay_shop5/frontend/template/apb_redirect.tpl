{strip}
    <div class="lpa-apb-redirect-wrapper">
        {if empty($lpaApb)}
            <div class="container">
                <div class="row">
                    <div class="col-12 col-xs-12 text-center">
                        <p class="alert alert-danger">{$oPlugin->getLocalization()->getTranslation('error_redirect')}</p>
                        <a href="{get_static_route id='bestellvorgang.php' params=['editZahlungsart' => 1]}" class="btn btn-primary mt-2">
                            {lang key="modifyPaymentOption" section="checkout"}
                        </a>
                    </div>
                </div>
            </div>
        {else}
            <div class="lpa-apb-redirect container">
                <div class="row">
                    <div class="col-12 col-xs-12">
                        <div class="lpa-apb-redirect-loader">
                            <i class="{$oPlugin->getLocalization()->getTranslation('apb_redirect_loader_icon')}"></i>
                        </div>
                    </div>
                    <div class="col-12 col-xs-12">
                        <div class="lpa-apb-redirect-text">
                            {$oPlugin->getLocalization()->getTranslation('apb_redirect_text')}
                        </div>
                    </div>
                    <div class="col-12 col-xs-12">
                        <div class="lpa-apb-redirect-link">
                            <a href="#" onclick="window.initAmazonPayCheckoutRedirect();return false;" title="{$oPlugin->getLocalization()->getTranslation('apb_redirect_link_label')}" rel="nofollow" class="lpa-apb-redirect-manual-link">{$oPlugin->getLocalization()->getTranslation('apb_redirect_link_label')}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display: none;">
                {$lpaApb.buttonHtml}
            </div>
        {/if}
    </div>
    <style>
        .lpa-apb-redirect-wrapper {
            position: fixed;
            height: 100vh;
            width: 100vw;
            top: 0;
            left: 0;
            z-index: 9999999;
            background-color: white;
            padding: 50px;
        }
        .lpa-apb-redirect-loader {
            color: #FF9900;
            text-align: center;
            margin-bottom: 30px;
        }
        .lpa-apb-redirect-text {
            margin-bottom: 10px;
            text-align: center;
        }
        .lpa-apb-redirect-link {
            text-align: center;
        }
    </style>
{/strip}