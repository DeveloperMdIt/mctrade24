{* NOTE: This template is only rendered when the user is on the Return page and in token-mode - it is rendered by the FrontendOutputController such that it appears directly in the <head> and does a redirect before showing any HTML to the user.
       All this script does is retranslate the location.hash into a GET parameter which will be then be handled by the return controller. *}
<script type="text/javascript">
    location.replace('//' + location.host + location.pathname + location.hash.replace('#','?'));
</script>