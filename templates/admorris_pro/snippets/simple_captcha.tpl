{block name='snippets-simple-captcha'}
    {input type="hidden" name=$captchaToken value=$captchaCode}
    {if isset($bAnti_spam_failed) && $bAnti_spam_failed}
        {block name='snippets-simple-captcha-msg'}
            <div class="simple-captcha form-error-msg" aria-live="assertive">
                {$admIcon->renderIcon('warning', 'icon-content icon-content--default')}
                {lang key='invalidToken'}
            </div>
        {/block}
    {/if}
{/block}
