<div>
    {if $baseCssURL|default:null !== null}
        <link media="screen" href="{$baseCssURL}" type="text/css" rel="stylesheet" />
    {/if}
    {foreach $modules as $module}
        <div class="jtlsearch_status_box panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{$module.name}</h3>
            </div>
            <div class="jtlsearch_inner">
                {if $module.cssURL|default:null !== null}
                    <link media="screen" href="{$module.cssURL}" type="text/css" rel="stylesheet" />
                {/if}
                <div class="jtlsearch_wrapper">
                    {$module.content}
                </div>
            </div>
        </div>
    {/foreach}
    <script type="text/javascript">
        $(function () {ldelim}
            $('.jtlsearch_wrapper').each(function () {ldelim}
                var heightActionColumn = $(this).children('.jtlsearch_actioncolumn').height(),
                    heightInfoColumn = $(this).children('.jtlsearch_infocolumn').height();
                if (heightActionColumn > heightInfoColumn) {ldelim}
                    $(this).children('.jtlsearch_infocolumn').height(heightActionColumn);
                    {rdelim} else if (heightActionColumn > 0) {ldelim}
                    $(this).children('.jtlsearch_actioncolumn').height(heightInfoColumn);
                    {rdelim}
                {rdelim});
            {rdelim});
    </script>
</div>
