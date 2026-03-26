{if $payment_method->module == 'Sviat/InvoicePayment'}
<div class="sviat_invoice_payment_cart_fields" data-payment-id="{$payment_method->id}" data-sviat-invoice-payment-api="{url_generator route='Sviat_InvoicePayment_companyByEdrpou' absolute=1}">
    <div class="f_row">
        <div class="f_col-md-4 f_col-lg-12 f_col-xl-4">
            <div class="form__group">
                <input class="form__input form__placeholder--focus"
                       name="payment_invoice_edrpou"
                       id="cart_payment_invoice_edrpou"
                       type="text"
                       value="{$request_data.payment_invoice_edrpou|escape}"
                       maxlength="20"
                       autocomplete="off"/>
                <span class="form__placeholder">{$lang->sviat__invoice_payment__edrpou|escape}*</span>
            </div>
        </div>
        <div class="f_col-md-8 f_col-lg-12 f_col-xl-8">
            <div class="form__group">
                <input class="form__input form__placeholder--focus"
                       name="payment_invoice_company_name"
                       id="cart_payment_invoice_company_name"
                       type="text"
                       value="{$request_data.payment_invoice_company_name|escape:'html'}"
                       autocomplete="off"/>
                <span class="form__placeholder">{$lang->sviat__invoice_payment__company_name|escape}*</span>
            </div>
        </div>
    </div>
</div>
<script>
    var form_enter_invoice_edrpou = "{$lang->sviat__invoice_payment__error_edrpou|escape:'javascript'}";
    var form_enter_invoice_company_name = "{$lang->sviat__invoice_payment__error_company_name|escape:'javascript'}";
</script>
{/if}
