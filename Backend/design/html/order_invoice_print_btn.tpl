{if $payment_method && $payment_method->module == 'Sviat/InvoicePayment' && $order->url}
    <span class="boxes_inline ml-h">
        <a href="{url_generator route='Sviat_InvoicePayment_invoicePrint' url=$order->url absolute=1}" target="_blank" class="btn btn_small btn-info" title="{$btr->sviat__invoice_payment__print_invoice_title|escape}">
            {include file="svg_icon.tpl" svgId="print"}
            <span class="ml-h">{$btr->sviat__invoice_payment__print_invoice|escape}</span>
        </a>
        <a href="{url_generator route='Sviat_InvoicePayment_invoiceWaybill' url=$order->url absolute=1}" target="_blank" class="btn btn_small btn_border-info ml-h" title="{$btr->sviat__invoice_payment__print_waybill_title|escape}">
            {include file="svg_icon.tpl" svgId="print"}
            <span class="ml-h">{$btr->sviat__invoice_payment__print_waybill|escape}</span>
        </a>
    </span>
{/if}
