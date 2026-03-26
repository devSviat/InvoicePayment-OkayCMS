(function() {
    function initEdrpouAutofill() {
        var containers = document.querySelectorAll('[data-sviat-invoice-payment-api]');
        containers.forEach(function(container) {
            var apiUrl = container.getAttribute('data-sviat-invoice-payment-api');
            if (!apiUrl) return;
            var edrpouInput = container.querySelector('input[name="payment_invoice_edrpou"]');
            var companyInput = container.querySelector('input[name="payment_invoice_company_name"]');
            if (!edrpouInput || !companyInput) return;
            if (edrpouInput._sviatInvoicePaymentBound) return;
            edrpouInput._sviatInvoicePaymentBound = true;

            function fetchCompany() {
                var edrpou = (edrpouInput.value || '').replace(/\D/g, '');
                if (edrpou.length < 8) return;
                var url = apiUrl + (apiUrl.includes('?') ? '&' : '?') + 'egrpou=' + encodeURIComponent(edrpou);
                fetch(url)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data && data.name) {
                            companyInput.value = data.name;
                            companyInput.classList.add('valid');
                            companyInput.setAttribute('aria-invalid', 'false');
                            var formGroup = companyInput.closest('.form__group');
                            if (formGroup) formGroup.classList.add('filled');
                        }
                    })
                    .catch(function() {});
            }

            edrpouInput.addEventListener('blur', fetchCompany);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEdrpouAutofill);
    } else {
        initEdrpouAutofill();
    }
})();
