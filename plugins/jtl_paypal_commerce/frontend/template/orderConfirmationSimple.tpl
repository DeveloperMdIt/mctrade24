{inline_script}<script>
    $(function() {
        $('#complete_order').on('submit', function() {
            history.pushState(null, null, '{$ppcStateURL}');

            return true;
        });
    });
</script>{/inline_script}