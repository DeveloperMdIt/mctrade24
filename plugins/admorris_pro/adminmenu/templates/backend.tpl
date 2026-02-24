
<link rel="stylesheet" type="text/css" href="{$oPlugin->getPaths()->getAdminURL()}css/styles.css?v={$oPlugin->getMeta()->getVersion()}" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="{$shopURL}/includes/libs/codemirror/addon/dialog/dialog.css?v={$oPlugin->getMeta()->getVersion()}" rel="stylesheet">

<div id="app"></div>

<script>
    var am_template_plugin_data = {$data};
    var shopData = {
      categories: {$categories},
      manufacturers: {$manufacturers}
    };

    /*
    (function () {
      const Classes = {
        collapseState: 'sidebar-collapsed'
      }

      const $sidebar = $('#sidebar');

      const storeKeySidebarState = 'jtlshop-sidebar-state';

      const setView = (state = false) => {
        $sidebar[state ? 'addClass' : 'removeClass'](Classes.collapseState)
        store.set(storeKeySidebarState, state)
      }
      setView('removeClass')
      
    })();
    */

</script>

<script type="text/javascript" charset="utf-8" src="{$appJsFilePath}"></script>


{literal}
<script type="text/javascript">
var $zoho=$zoho || {};$zoho.salesiq = $zoho.salesiq || {widgetcode:"cf25cc4fdced4ae48b16f3e5893f010ed81be9090602422e32316f4a8bdb9ef7f63927cf3de66c5c4fe36258013845f6", values:{},ready:function(){}};var d=document;s=d.createElement("script");s.type="text/javascript";s.id="zsiqscript";s.defer=true;s.src="https://salesiq.zoho.eu/widget";t=d.getElementsByTagName("script")[0];t.parentNode.insertBefore(s,t);d.write("<div id='zsiqwidget'></div>");
</script>
{/literal}