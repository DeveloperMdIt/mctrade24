{block name='productdetails-config-container'}

    <section aria-labelledby="product-configuration-sidebar-heading" id="cfg-container" tabindex="-1" class="row product-configuration-container">
        {col cols=12 lg=8}
            {include file='productdetails/config_options_list.tpl'}
        {/col}
        {col cols=12 lg=4}
            <div id="product-configuration-sidebar" class="product-configuration-sidebar-wrapper card card-body sticky-top">
                <div class="panel panel-primary no-margin">
                    <div class="panel-heading">
                        <h2 id="product-configuration-sidebar-heading" class="panel-title h2">{lang key='yourConfiguration'}</h2>
                    </div>
                    <table class="table table-sm config-table">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">{lang section='productDetails' key='configTableHeaderProduct'}</th>
                                <th scope="col">{lang section='productDetails' key='configTableHeaderPrice'}</th>
                            </tr>
                        </thead>
                        <tbody class="summary"></tbody>
                        <tfoot>
                        <tr>
                            <td colspan="3" class="text-right word-break">
                                <strong class="price"></strong>
                                <div class="vat_info text-muted">
                                    <small>{include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}</small>
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                    <div class="panel-footer product-configuration-basket-add">
                        {include file='productdetails/basket.tpl'}
                    </div>
                </div>
            </div>
        {/col}
    </section>
 
{/block}
