{if $payment_method->module == 'Sviat/InvoicePayment'}
<div class="mb-1 sviat_invoice_payment_admin_edrpou_wrap">
    <div class="heading_label">{$btr->sviat__invoice_payment__edrpou|escape}</div>
    <input name="sviat_invoice_payment_edrpou" id="admin_sviat_invoice_payment_edrpou" class="form-control" type="text" value="{$invoice_payment_data->edrpou|default:''|escape:'html'}" maxlength="20" data-api-url="{$invoice_payment_api_url|escape}" required />
</div>
<div class="mb-1">
    <div class="heading_label">{$btr->sviat__invoice_payment__company_name|escape}</div>
    <input name="sviat_invoice_payment_company_name" id="admin_sviat_invoice_payment_company_name" class="form-control" type="text" value="{$invoice_payment_data->company_name|default:''|escape:'html'}" required />
</div>
<script>
(function() {
    var edrpouInput = document.getElementById('admin_sviat_invoice_payment_edrpou');
    var companyInput = document.getElementById('admin_sviat_invoice_payment_company_name');
    if (!edrpouInput || !companyInput) return;
    var apiUrl = edrpouInput.getAttribute('data-api-url');
    if (!apiUrl) return;
    edrpouInput.addEventListener('blur', function() {
        var edrpou = (edrpouInput.value || '').replace(/\D/g, '');
        if (edrpou.length < 8) return;
        var url = apiUrl + (apiUrl.includes('?') ? '&' : '?') + 'egrpou=' + encodeURIComponent(edrpou);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.name) {
                    companyInput.value = data.name;
                }
            })
            .catch(function() {});
    });
})();
</script>
{/if}
