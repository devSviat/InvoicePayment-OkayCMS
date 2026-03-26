$(function(){
    var $cart = $('.fn_validate_cart');
    var $block = $('.sviat_invoice_payment_cart_fields');
    if (!$cart.length || !$block.length) return;
    var paymentId = $block.data('payment-id');
    if (paymentId == null || typeof form_enter_invoice_edrpou === 'undefined' || typeof form_enter_invoice_company_name === 'undefined') return;
    var isInvoiceSelected = function() {
        return $('input[name="payment_method_id"]:checked').val() == String(paymentId);
    };
    var fields = [
        { name: 'payment_invoice_edrpou', message: form_enter_invoice_edrpou },
        { name: 'payment_invoice_company_name', message: form_enter_invoice_company_name }
    ];
    fields.forEach(function(f) {
        $cart.find('input[name="' + f.name + '"]').rules('add', {
            required: { depends: isInvoiceSelected },
            messages: { required: f.message }
        });
    });
});
