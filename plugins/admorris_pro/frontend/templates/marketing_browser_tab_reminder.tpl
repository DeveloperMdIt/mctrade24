<script type="module">
    $(function() {
        // Get page title
        let pageTitle = $("title").text();
        let toAppend = "{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_browser_tab_reminder_text')}";
        let newPageTitle;

        //add no title to reminder 
        {if $admorris_pro_marketing_browser_tab_reminder_add_title === 'N'}
          newPageTitle = toAppend;
        {/if}

        //add title before reminder
        {if $admorris_pro_marketing_browser_tab_reminder_add_title === '1'}
          newPageTitle = pageTitle + " - " + toAppend;
        {/if}

        //add title after reminder
        {if $admorris_pro_marketing_browser_tab_reminder_add_title === '2'}
          newPageTitle = toAppend + " - " + pageTitle;
        {/if}

        // Change page title on blur
        $(window).blur(function() {
            $("title").html(newPageTitle);
        });

        // Change page title back on focus
        $(window).focus(function() {
            $("title").text(pageTitle);
        });
    });
</script>