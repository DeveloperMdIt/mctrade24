{block name='account-uploads'}
{if !empty($Bestellung->oUpload_arr)}
    {assign var=nNameLength value=50}
    {assign var=nImageMaxWidth value=480}
    {assign var=nImageMaxHeight value=320}
    {assign var=nImagePreviewWidth value=35}
    <div id="uploads">
    {block name='account-uploads-subheading'}
        <h2 class="h3">{lang key='yourUploads'}</h2>
        {/block}
            {block name='account-uploads-content'}
        <div class="table-responsive">
            <table class="table table-striped table-bordered" id="customerupload">
                <thead>
                    {block name='account-uploads-uploads-heading'}
                    <tr>
                        <th class="text-center">{lang key='name' section='global'}</th>
                        <th class="text-center">{lang key='uploadFilesize' section='global'}</th>
                        <th class="text-center">{lang key='uploadAdded' section='global'}</th>
                        <th class="text-center">{lang key='uploadFile' section='global'}</th>
                    </tr>
                    {/block}
                </thead>
                <tbody>
                {block name='account-uploads-uploads'}
                {foreach $Bestellung->oUpload_arr as $oUpload}
                    <tr>
                        <td class="text-center vcenter">{$oUpload->cName}</td>
                        <td class="text-center vcenter">{$oUpload->cGroesse}</td>
                        <td class="text-center vcenter">
                            <span class="infocur" title="{$oUpload->dErstellt|date_format:'d.m.Y - H:i:s'}">
                                {$oUpload->dErstellt|date_format:'d.m.Y'}
                            </span>
                        </td>
                        <td class="text-center">
                            {form method="post" action="{get_static_route id='jtl.php'}" slide=true}
                            {input name="kUpload" type="hidden" value=$oUpload->kUpload}
                                {block name='account-uploads-uploads-button'}
                                    {button type="submit" size="sm" variant="outline-primary" name=$oUpload->cName}
                                        {$admIcon->renderIcon('download', 'icon-content icon-content--default')}
                                    {/button}
                                {/block}
                            {/form}
                        </td>
                    </tr>
                {/foreach}
                {/block}
                </tbody>
            </table>
        </div>
        {/block}
    </div>
{/if}
{/block}