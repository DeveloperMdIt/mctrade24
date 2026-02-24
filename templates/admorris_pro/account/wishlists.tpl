{*custom*}

 
{block name='account-wishlists'}
<h1 class="menu-title mt-0">{block name='account-wishlist-title'}{lang key='yourWishlist' section='login'}{/block}</h1>
{if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
    {block name='account-wishlist'}
        {block name='account-wishlist-body'}
            {if !empty($oWunschliste_arr[0]->kWunschliste)}
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th>{lang key='wishlistName' section='login'}</th>
                            <th>{lang key='wishlistStandard' section='login'}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $oWunschliste_arr as $Wunschliste}
                            <tr>
                                 <td><a href="{get_static_route id='wunschliste.php'}?wl={$Wunschliste->kWunschliste}">{$Wunschliste->cName}</a></td>
                                <td>{if $Wunschliste->nStandard == 1}{lang key='active' section='global'}{/if} {if $Wunschliste->nStandard == 0}{lang key='inactive' section='global'}{/if}</td>
                                <td class="text-right">
                                    <form method="post" action="{get_static_route id='jtl.php'}?wllist=1">
                                        <input type="hidden" name="wl" value="{$Wunschliste->kWunschliste}"/>
                                        {$jtl_token}
                                        <span class="am-spaced-button-group btn-group-sm">
                                            {if $Wunschliste->nStandard != 1}
                                                <button class="btn btn-secondary" name="wls" value="{$Wunschliste->kWunschliste}" title="{lang key='wishlistStandard' section='login'}">
                                                {$admIcon->renderIcon('check', 'icon-content icon-content--default')} {lang key='wishlistStandard' section='login'}
                                                </button>
                                            {/if}
                                            {if $Wunschliste->nOeffentlich == 1}
                                                <button type="submit" class="btn btn-secondary" name="wlAction" value="setPrivate" title="{lang key='wishlistPrivat' section='login'}">
                                                    {$admIcon->renderIcon('eyeSlash', 'icon-content icon-content--default')} {lang key='wishlistSetPrivate' section='login'}
                                                </button>
                                            {/if}
                                            {if $Wunschliste->nOeffentlich == 0}
                                                <button type="submit" class="btn btn-secondary" name="wlAction" value="setPublic" title="{lang key='wishlistNotPrivat' section='login'}">
                                                    {$admIcon->renderIcon('eye', 'icon-content icon-content--default')} {lang key='wishlistNotPrivat' section='login'}
                                                </button>
                                            {/if}
                                            <button type="submit" class="btn btn-danger" name="wllo" value="{$Wunschliste->kWunschliste}" title="{lang key='wishlisteDelete' section='login'}">
                                                {$admIcon->renderIcon('trash', 'icon-content icon-content--default')}
                                            </button>
                                        </span>
                                    </form>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {/if}
            <form method="post" action="{get_static_route id='jtl.php'}?wllist=1" class="form form-inline">
                {$jtl_token}
                <input name="wlh" type="hidden" value="1" />
                <div class="input-group">
                    <input name="cWunschlisteName" type="text" class="form-control form-control-sm" placeholder="{lang key='wishlistAddNew' section='login'}" size="25" aria-label="{lang key='wishlistAddNew' section='login'}">
                    <span class="input-group-append">
                        <input type="submit" class="btn btn-primary btn-sm" name="submit" value="{lang key='wishlistSaveNew' section='login'}" />
                    </span>
                </div>
            </form>
        {/block}
    {/block}
{/if}
{/block}