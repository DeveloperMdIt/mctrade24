<script>
{ldelim}
let id = '#setting_{$settingsName}';
let cssClass = '.{$settingsName}_toggle';
{literal}
    $(document).ready(function() {
        if ($(id).val() === 'Y') {
            $(cssClass).removeClass('d-none');
        }
        $(id).on('change',function() {
            if ($(this).val() === 'Y') {
              $(cssClass).removeClass('d-none');
            }
            if ($(this).val() === 'N') {
                $(cssClass).addClass('d-none');
            }
        })
    })
{/literal}
{rdelim}
</script>