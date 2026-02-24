{* admorris pro custom - row-gap, cols modified & btn-block *}
{block name='account-address-form'}
    {block name='account-address-form-form-rechnungsdaten'}
        {form method="post" id='rechnungsdaten' action="{get_static_route params=['editRechnungsadresse' => 1]}" class="jtl-validate" slide=true}
            <div id="panel-address-form">
                {block name='account-address-form-include-inc-billing-address-form'}
                    {include file='checkout/inc_billing_address_form.tpl'}
                {/block}
                {block name='account-address-form-form-submit'}
                    {row class='btn-row row-gap-3 mt-3'}
                        {col md=4 cols=12}
                            {link class="btn btn-outline-primary btn-back btn-block"  href="{get_static_route id='jtl.php'}"}
                                {lang key='back'}
                            {/link}
                        {/col}
                        {col md=8 cols=12 class="checkout-button-row-submit"}
                            {input type="hidden" name="editRechnungsadresse" value="1"}
                            {input type="hidden" name="edit" value="1"}
                            {button type="submit" value="1" block=true variant="primary"}
                                {lang key='editCustomerData' section='account data'}
                            {/button}
                        {/col}
                    {/row}
                {/block}
            </div>
        {/form}
    {/block}
{/block}
