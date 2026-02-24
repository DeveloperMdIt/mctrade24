{block name="layout-footer-js" append}
    {inline_script}
        <script>
            window.addEventListener('load', function () {
                window.jtl_search_token = '{$smarty.session.jtl_token}';
                if (typeof $.fn.jtl_search !== 'undefined') {
                    {if !$isMobile || $isTablet}
                    $('#search-header').jtl_search({
                        'align': '{$jtl_search_align}',
                        'url': '{$jtl_search_frontendURL}'
                    });
                    {/if}
                    $('#search-header-mobile-top').jtl_search({
                        'align': 'full-width',
                        'url': '{$jtl_search_frontendURL}',
                        'class': 'jtl-search-mobile-top'
                    });
                    $('#search-header-mobile-fixed').jtl_search({
                        'align': 'full-width',
                        'url': '{$jtl_search_frontendURL}',
                        'class': 'jtl-search-mobile-fixed'
                    });
                    {if isset($Einstellungen.template.header.mobile_search_type)
                    && $Einstellungen.template.header.mobile_search_type === 'dropdown'}
                    $('#search-header-desktop').jtl_search({
                        'align': 'full-width',
                        'url': '{$jtl_search_frontendURL}',
                        'class': 'jtl-search-mobile-dropdown'
                    });
                    {/if}
                }
            });
        </script>
    {/inline_script}
{/block}
