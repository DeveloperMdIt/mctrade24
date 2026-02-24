{*custom*}
{* HEADER NAV *}
{* includes also slider & banner templates
   because the need to be in header-wrapper for the fullscreen design *}

{* {$slider_layout = $Einstellungen.template.theme.slider} *}
{$header_wrapper_class=""}
{$slider = null}

{if !empty($amSlider)}
    {if $admPro->checkIfMobileScreen() === 1}
        {$slider = $amSlider->mobileSlides}
    {else}
        {$slider = $amSlider->desktopSlides}
    {/if}
{/if}

{if !empty($slider)}
    {if $amSlider->fullscreen === 'fullscreen' }
        {$header_wrapper_class=' header-wrapper--fullscreen'}
    {/if}
    {if $amSlider->fullscreen === 'fullscreen_framed'}
        {$header_wrapper_class = $header_wrapper_class|cat:'  header-wrapper--framed'}
    {/if}

    {$fullscreen = (in_array($amSlider->fullscreen, ['fullscreen', 'fullscreen_framed']))?true:false}

    {if $admorris_pro_templateSettings->header_overlay}
        {$header_wrapper_class = $header_wrapper_class|cat:' header-wrapper--overlay'}
    {/if}
    {* {$header_wrapper_class="transparent-header"} *}
{/if}
{if isset($admBlogSettings)}
    {$admBlogSettings->initBanner()}
    {if $admBlogSettings->getHeaderOverlay($admorris_pro_templateSettings)}
        {$header_wrapper_class = $header_wrapper_class|cat:' header-wrapper--overlay'}
    
    {/if}
{/if}

{$headerLayout = $admPro->initHeaderLayout()}

{if !isset($activeId)}
    {if $NaviFilter->hasCategory()}
        {$activeId = $NaviFilter->getCategory()->getValue()}
    {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($Artikel)}
        {assign var=activeId value=$Artikel->gibKategorie()}
    {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($smarty.session.LetzteKategorie)}
        {$activeId = $smarty.session.LetzteKategorie}
    {else}
        {$activeId = 0}
    {/if}
{/if}


<div class="header-wrapper{$header_wrapper_class}">
    {*
        Hack for icons parent container display toggling - if display is set to none all style ids will be removed from the dom
        --> i. e. icons with same style id's seem to be visibility-hidden
        so to keep the styling we rnder the specific dummy icon on top of the page with height/width set to 0
    *}
    {* {$admIcon->renderIcon('warning', "icon-dummy")} *}
    <header
        class="header d-print-none {if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid'}container-block{/if} {$headerLayout->classes}"
        id="jtl-nav-wrapper">
        <div id="evo-main-nav-wrapper"
            class="header__nav-wrapper{if $admorris_pro_templateSettings->boxedHeader} boxed-header{/if}">
            <div class="header__desktop-nav header__nav">
                <div
                    id="header-container" {if isset($admorris_pro_themeVars->headerDropdownAnimation)}data-dropdown-animation="{$admorris_pro_themeVars->headerDropdownAnimation}"{/if}>
                    {block name="header-container-inner"}
                        {include file="header/header_layout.tpl" layoutType='desktopLayout'}
                    {/block}
                </div>{* /container *}
            </div>

            <div id="shop-nav-xs" class="header__mobile-nav header__nav mobile-navbar {*navbar navbar-light bg-light*} ">
                {include file="header/header_layout.tpl" layoutType='mobileLayout'}
            </div>

        </div>{* /#evo-main-nav-wrapper *}
    </header>

    {include file="offcanvas/offcanvas_nav.tpl"}


    {* {$headerLayout|var_dump} *}

    {opcMountPoint id='opc_after_header-navigation' title="Nach <header>, vor Admorris Pro Slider" inContainer=false}
    {if isset($amSlider) && count($amSlider->slide_arr) > 0}
        {include file="slider/am_slider.tpl"}
    {/if}

</div>