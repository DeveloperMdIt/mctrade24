{*custom*}
{block name='layout-footer'}


{block name='layout-footer-content-all-closingtags'}
    {block name='layout-footer-content-closingtag'}
        {opcMountPoint id='opc_content'}
    </div>{* /content *}
    {/block}

    {block name='layout-footer-sidepanel-left'}
        {has_boxes position='left' assign='hasLeftBox'}

        {if !$bExclusive && $hasLeftBox && $Einstellungen.template.theme.left_sidebar === 'Y' && !empty($boxes.left|strip_tags|trim) &&  $nSeitenTyp !== $smarty.const.PAGE_ARTIKELLISTE }
        {* product list page has a differentgrid-row *}
            {block name="footer-sidepanel-left"}
            <aside id="sidepanel_left"
                class="left-sidebar-boxes sidebar-layout__sidebar d-print-none">
                {block name="footer-sidepanel-left-content"}{$boxes.left}{/block}
            </aside>
            {/block}
        {/if}
    {/block}
    
    {* {block name='content-row-closingtag'}
    </div>
    {/block} *}{* /row *}
    
    {block name='content-container-closingtag'}
    </div>{* /container *}
    {/block}
    
    {block name='layout-footer-content-wrapper-closingtag'}
        </div>{* /content-wrapper*}
        {opcMountPoint id='opc_after_content' inContainer=false}
    {/block}
{/block}

{block name='layout-footer-main-wrapper-closingtag'}
</main> {* /main-wrapper *}
{/block}

{block name='layout-footer-content'}
{if !$bExclusive}
    <footer id="footer" class="footer">
        {opcMountPoint id='opc_before_footer_container' inContainer=false}

        <div class="footer-container admPro-container container--{$admPro->container_size_footer()} stack d-print-none">

            {opcMountPoint id='opc_before_footer_boxes' title='Vor Footer Boxen'}
                
            {getBoxesByPosition position='bottom' assign='footerBoxes'}
            {$footerBoxCount = (!empty($footerBoxes)) ? count($footerBoxes) : 0}

            <div class="row row-gap footer-boxes{if $footerBoxCount < 4} footer-boxes--centered{/if}" data-box-count="{$footerBoxCount}" id="footer-boxes">
                {block name='footer-boxes'}
                {if isset($footerBoxes) && count($footerBoxes) > 0}
                    {foreach $footerBoxes as $box}
                        <div class="{block name='footer-boxes-class'}col col-12 col-md-6 col-lg-3{/block}">
                            {$box->getRenderedContent()}
                        </div>
                    {/foreach}
                {/if}
                {/block}

                {block name='footer-additional'}
                {if $Einstellungen.template.footer.socialmedia_footer === 'Y' || $Einstellungen.template.footer.newsletter_footer === 'Y'}
                <div class="footer-grid-newsletter-and-social col col-12 col-xl-3"> {* col-xl-3 changes the looking of this box. Check the css *}
                    <div class="newsletter-and-social newsletter_n_social am-flexgrid">
                        {if $Einstellungen.template.footer.newsletter_footer === 'Y'
                            && $Einstellungen.newsletter.newsletter_active === 'Y'}
                            {block name='footer-newsletter'}
                                <div class="newsletter-info">
                                    <div class="newsletter-info-heading">
                                        <div class="product-filter-headline"><span>{lang key="newsletter" section="newsletter"}</span> <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span></div>
                                    </div>
                                    {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ])}
                                        {block name='layout-footer-newsletter-info'}
                                        <div class="newsletter-info-body links-underline">
                                            {lang key='newsletterInformedConsent' section='newsletter' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL()}
                                        </div>
                                        {/block}
                                    {/if}
                                    {* <p class="info hidden-sm hidden-md">{lang key='newsletterInformedConsent' section='newsletter' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL()}</p> *}
                                </div>
                                <div class="newsletter-body">
                                    {form methopd="post" action="{get_static_route id='newsletter.php'}"}
                                        {block name='layout-footer-form-content'}
                                            <input type="hidden" name="abonnieren" value="2"/>
                                            <div class="form-group">
                                                <label class="col-form-label sr-only" for="newsletter_email">{lang key='emailadress'}</label>
                                                <div class="newsletter-input-group">
                                                    <input type="email" size="20" name="cEmail" id="newsletter_email" class="newsletter-form-control form-control" placeholder="{lang key='emailadress'}">
                                                    <button type="submit" class="btn btn-primary submit newsletter-submit">
                                                        <span class="d-none d-sm-inline">{lang key="newsletter" section="newsletter"}</span> <span> {lang key="newsletterSendSubscribe" section="newsletter"}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        {/block}
                                        {block name='layout-footer-form-captcha'}
                                            <div class="{if !empty($plausiArr.captcha) && $plausiArr.captcha === true} has-error{/if}">
                                                {captchaMarkup getBody=true}
                                            </div>
                                        {/block}
                                    {/form}
                                </div>
                            {/block}
                        {/if}

                        {if $Einstellungen.template.footer.socialmedia_footer === 'Y'}
                            {$socialmediaArr = $admPro->getSocialmedia()}
                            {if !empty($socialmediaArr)}
                                <div class="footer-socialmedia">
                                    {if count($footerBoxes) < 4 && $Einstellungen.template.footer.newsletter_footer !== 'Y'}
                                    <div class="socialmedia-header">
                                        <div class="product-filter-headline">{lang key='social' section='custom'}</div>
                                    </div>
                                    {/if}
                                    <ul class="footer-socialmedia__list cluster list-unstyled">
                                        {block name='footer-socialmedia'}
                                            {foreach $socialmediaArr as $item}
                                                {if !empty($item.link)}
                                                    <li class="footer-socialmedia__item">
                                                        {if ($item.name === "googleplus") } 
                                                            {$item.name = "googlePlus"}
                                                        {/if}
                                                        <a href="{$item.link}" class="btn-social btn-{$item.name} text-center" title="{$item.title}" aria-label="{$item.title}" target="_blank" rel="noopener">{$admIcon->renderIcon($item.name, 'icon-content icon-content--default')}</a>
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        {/block}
                                    </ul>
                                </div>
                            {/if}
                        {/if}
                    </div>{* /footer-additional *}
                </div>
                {/if}
                {/block}{* /footer-additional *}
            </div>

            {opcMountPoint id='opc_before_pay_icons' title='Vor Zahlungsicons'}
            
            {block name="payment_icons"}
                {if isset($admorris_pro_templateSettings->paymentIcons) && !empty($admorris_pro_templateSettings->paymentIcons) && $admorris_pro_templateSettings->paymentProviderLogosFooter}
                    <section class="pay-icons-wrapper" aria-labelledby="payment_icons-title">
                        <h2 id="payment_icons-title" class="sr-only">{lang key="paymenticons" section="aria"}</h2>
                        <ul id="payment_icons" class="pay-icons cluster list-unstyled">
                            {foreach from=$admorris_pro_templateSettings->paymentIcons item=icon key=key}
                                <li class="pay-icons__icon pf">
                                    {$admIcon->usePaymentIcon($icon)}
                                </li>
                            {/foreach}
                        </ul>
                    </section>
                {/if}
            {/block}

            {opcMountPoint id='opc_before_vat_info'}
            
            <div class="footnote-vat text-center links-underline">
                {if $NettoPreise == 1}
                    {lang key="footnoteExclusiveVat" section="global" assign="footnoteVat"}
                {else}
                    {lang key="footnoteInclusiveVat" section="global" assign="footnoteVat"}
                {/if}
                {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
                    {if $Einstellungen.global.global_versandhinweis === 'zzgl'}
                        {lang key="footnoteExclusiveShipping" section="global" printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign="footnoteShipping"}
                    {elseif $Einstellungen.global.global_versandhinweis === 'inkl'}
                        {lang key="footnoteInclusiveShipping" section="global" printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL() assign="footnoteShipping"}
                    {/if}
                {/if}
                {block name="footer-vat-notice"}
                    <span class="footnote-reference">*</span> {$footnoteVat}{if isset($footnoteShipping)}{$footnoteShipping}{/if}
                {/block}
            </div>

                {* custom - consent settings moved to footer *}
                {if $Einstellungen.consentmanager.consent_manager_active === 'Y' && !$isAjax && $consentItems->isNotEmpty()} 
                    <div class="text-center"><button type="button" id="consent-settings-btn" class="btn btn-link btn-inline btn--consent-settings text-capitalize text-decoration-underline">{lang key='cookieSettings' section='consent'}</button></div>
                {/if}

        </div>{* /container *}


        {** 
         * empty .container for trusted shops plugin to insert the reviews. 
         * the default selector of trusted shops does not work, because we changed the footer container to 'admPro-container'
         * Trusted Shops has no selector option
         *}
        <div class="container"></div>

        {opcMountPoint id='opc_before_copyright' title='Default Area' inContainer=false}

        <div id="copyright">
            {block name='footer-copyright'}
                <div class="admPro-container copyright-container">
                    {assign var=isBrandFree value=\JTL\Shop::isBrandfree()}

                    <ul class="list-unstyled">
                        {if !empty($meta_copyright) || $Einstellungen.global.global_zaehler_anzeigen === 'Y'}
                            <li class="text-center">
                                {if !empty($meta_copyright)}<span itemprop="copyrightHolder">&copy; {$meta_copyright}</span>{/if}
                                {if $Einstellungen.global.global_zaehler_anzeigen === 'Y'}{lang key="counter" section="global"}: {$Besucherzaehler}{/if}
                            </li>
                        {/if}
                        {if !empty($Einstellungen.global.global_fusszeilehinweis)}
                            <li class="text-center">
                                {$Einstellungen.global.global_fusszeilehinweis}
                            </li>
                        {/if}
                        <li id="branding" class="text-center">
                            {if !$isBrandFree}
                                <span id="system-credits" class="text-center">Powered by <a href="http://jtl-url.de/jtlshop" title="JTL-Shop" target="_blank" rel="noopener">JTL-Shop</a></span>
                            {/if}
                        </li>
                    </ul>
                </div>
            {/block}
        </div>
    </footer>
{/if}
{/block}



{block name='scroll-to-top'}
    {if $admorris_pro_templateSettings->scroll_to_top_active}
    {block name='scroll-to-top-inner'}
        <div class="scroll-to-top scroll-to-top--is-intersecting">
            <a href="#skip-navigation-link" {if $admorris_pro_templateSettings->scroll_to_top_button_content === 'icon'}aria-label="{lang key="scrollToTopButton" section="custom"}"{/if}class="scroll-to-top__button button-reset">
                {block name='scroll-to-top-button-content'}
                    {if $admorris_pro_templateSettings->scroll_to_top_button_content === 'text'}
                        <div class="scroll-to-top__text">
                            {lang key="scrollToTopButton" section="custom"}
                        </div>
                    {elseif $admorris_pro_templateSettings->scroll_to_top_button_content === 'icon'}
                        {$admIcon->renderIcon('angleUp', 'scroll-to-top__icon icon-content icon-content--center')}
                    {elseif $admorris_pro_templateSettings->scroll_to_top_button_content === 'icon_text'}
                        {$admIcon->renderIcon('angleUp', 'scroll-to-top__icon icon-content icon-content--center')}&ensp;<div class="scroll-to-top__text">
                            {lang key="scrollToTopButton" section="custom"}
                        </div>
                    {/if}
                {/block}
            </a>
        </div>
    {/block}
    {/if}
{/block}

{* JavaScripts *}
{block name="footer-js"}
    {$dbgBarBody}

    {captchaMarkup getBody=false}

    {block name='layout-footer-js'}{/block}

    {block name='layout-footer-io-path'}
        <div id="jtl-io-path" data-path="{$ShopURL}" class="d-none"></div>
    {/block}
{/block}
<div id="fixed-bottom-pixel" class="fixed-bottom-pixel"></div>
</body>
</html>
{/block}