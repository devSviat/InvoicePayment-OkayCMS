<div class="sviat_invoice_payment_order_wrap" data-sviat-invoice-payment-api="{$invoice_payment_api_url|escape}" data-invoice-print-url="{$invoice_print_url|escape:'html'}">
    <div class="sviat_invoice_payment_order_view fn_sviat_invoice_payment_view" style="{if !$edrpou && !$company_name}display:none;{/if}">
        {if $edrpou || $company_name}
        <div class="sviat_invoice_payment">
            {if $edrpou}
            <div class="sviat_invoice_payment__detail">
                <span class="sviat_invoice_payment__label">{$lang->sviat__invoice_payment__edrpou|escape}</span>
                <span class="sviat_invoice_payment__value">{$edrpou|escape}</span>
            </div>
            {/if}
            {if $company_name}
            <div class="sviat_invoice_payment__detail">
                <span class="sviat_invoice_payment__label">{$lang->sviat__invoice_payment__company_name|escape}</span>
                <span class="sviat_invoice_payment__value">{$company_name|escape}</span>
            </div>
            {/if}
        </div>
        <form class="order_payment__form sviat_invoice_payment_order_buttons" method="post">
            <button type="button" class="form__button fn_sviat_invoice_payment_view_btn" name="view_invoice" data-language="sviat_invoice_payment_view_invoice">{$lang->sviat__invoice_payment__view_invoice|escape}</button>
            {* <button type="button" class="form__button fn_sviat_invoice_payment_download_btn" name="download_invoice" data-language="sviat_invoice_payment_download_invoice">{$lang->sviat__invoice_payment__download_invoice|escape}</button> *}
        </form>
        {/if}
    </div>
</div>
<script>
(function() {
    var wrap = document.querySelector('.sviat_invoice_payment_order_wrap');
    if (!wrap) return;
    var printUrl = wrap.getAttribute('data-invoice-print-url');
    var viewBtn = wrap.querySelector('.fn_sviat_invoice_payment_view_btn');
    var downloadBtn = wrap.querySelector('.fn_sviat_invoice_payment_download_btn');

    function initViewButton() {
        if (!viewBtn || !printUrl) return;
        viewBtn.addEventListener('click', function() {
            window.open(printUrl, '_blank', 'noopener');
        });
    }

    if (downloadBtn && printUrl) {
        downloadBtn.addEventListener('click', function() {
            var url = printUrl + (printUrl.indexOf('?') >= 0 ? '&' : '?') + 'print=1';
            window.open(url, '_blank', 'noopener');
        });
    }

    if (document.readyState === 'complete') {
        initViewButton();
    } else {
        window.addEventListener('load', initViewButton);
    }
})();
</script>
