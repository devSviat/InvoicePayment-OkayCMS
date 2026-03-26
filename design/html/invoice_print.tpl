<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$document_title|escape} {$order->id}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 20px; font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .invoice { max-width: 800px; margin: 0 auto; }
        .invoice__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .invoice__header-title-wrap { flex: 1; min-width: 0; padding-bottom: 6px; border-bottom: 2px solid #000; }
        .invoice__header-title { font-size: 16px; font-weight: bold; line-height: 1.3; margin: 0; }
        .invoice__section { margin-bottom: 20px; }
        .invoice__section-row { display: table; width: 100%; margin-bottom: 16px; }
        .invoice__section-label { display: table-cell; width: 22%; vertical-align: top; padding-right: 12px; font-size: 12px; }
        .invoice__section-value { display: table-cell; vertical-align: top; font-size: 12px; }
        .invoice__section-value-heading { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .invoice__goods-title { font-size: 12px; font-weight: normal; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .invoice__goods .col-no { width: 40px; text-align: center; }
        .invoice__goods .col-qty { width: 80px; text-align: center; }
        .invoice__goods .col-unit { width: 50px; text-align: center; }
        .invoice__goods .col-price { width: 100px; text-align: right; }
        .invoice__goods .col-sum { width: 110px; text-align: right; font-weight: bold; }
        .invoice__goods tfoot td { border: none; }
        .invoice__total-line { font-weight: bold; margin-top: 12px; }
        .invoice__total-items { margin: 0 0 2px 0; }
        .invoice__amount-words { font-weight: bold; margin: 0; }
        .invoice__place { margin-top: 12px; padding-bottom: 6px; border-bottom: 2px solid #000; }
        .invoice__signatures { margin-top: 32px; }
        .invoice__signatures-row { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 0; }
        .invoice__signature-block { flex: 1; max-width: 48%; }
        .invoice__signature-label { margin-bottom: 4px; font-size: 12px; }
        .invoice__signature-line { border-bottom: 1px solid #333; min-height: 24px; margin-bottom: 4px; min-width: 120px; }
        .invoice__signature-name { font-size: 10px; }
        .invoice__signature-note { font-size: 10px; font-style: italic; margin-top: 12px; color: #555; }
        .invoice__signature-proxy { font-size: 10px; margin-top: 12px; color: #555; }
        .invoice__signature-proxy-gap { display: inline-block; min-width: 2em; }
        .invoice__signature-proxy-gap--wide { min-width: 10em; }
        .invoice__actions { display: flex; gap: 10px; flex-shrink: 0; }
        .invoice__actions button { padding: 8px 16px; font-size: 12px; cursor: pointer; border: 1px solid #333; background: #fff; border-radius: 4px; }
        .invoice__actions button:hover { background: #f5f5f5; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 15px; }
            .invoice { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="invoice__header">
            <div class="invoice__header-title-wrap">
                <p class="invoice__header-title">{$document_title|escape} №{$order->id} {$invoice_date_formatted|escape}</p>
            </div>
            <div class="invoice__actions no-print">
                <button type="button" onclick="window.print();">{$invoice_lang->sviat__invoice_payment__print|escape}</button>
            </div>
        </div>

        <section class="invoice__section">
            <div class="invoice__section-row">
                <div class="invoice__section-label">{$invoice_lang->sviat__invoice_payment__supplier|escape}:</div>
                <div class="invoice__section-value">
                    <div class="invoice__section-value-heading">{if $invoice_settings->supplier_type}{$invoice_settings->supplier_type|escape} {/if}{$invoice_settings->supplier_name|escape}</div>
                    {if $invoice_settings->supplier_bank_account || $invoice_settings->supplier_bank_name}
                    <div>{$invoice_lang->sviat__invoice_payment__bank_account|escape} {$invoice_settings->supplier_bank_account|escape}{if $invoice_settings->supplier_bank_name} {$invoice_lang->sviat__invoice_payment__bank_name|escape} {$invoice_settings->supplier_bank_name|escape},{/if}</div>
                    {/if}
                    {if $invoice_settings->supplier_address}
                    <div>{$invoice_settings->supplier_address|escape}</div>
                    {/if}
                    {if $invoice_settings->supplier_recipient_code}
                    <div>{$invoice_lang->sviat__invoice_payment__recipient_code|escape} {$invoice_settings->supplier_recipient_code|escape},</div>
                    {/if}
                    {if $invoice_settings->supplier_tax_info}
                    <div>{$invoice_settings->supplier_tax_info|escape}</div>
                    {/if}
                </div>
            </div>
        </section>

        <section class="invoice__section">
            <div class="invoice__section-row">
                <div class="invoice__section-label">{$invoice_lang->sviat__invoice_payment__buyer|escape}:</div>
                <div class="invoice__section-value">
                    <div class="invoice__section-value-heading">{$invoice->company_name|escape}</div>
                    <div class="invoice__section-value-line">{$invoice_lang->sviat__invoice_payment__edrpou|escape} {$invoice->edrpou|escape}</div>
                </div>
            </div>
        </section>

        <section class="invoice__section">
            <p class="invoice__goods-title">{$invoice_lang->sviat__invoice_payment__goods|escape}:</p>
            <table class="invoice__goods">
                <thead>
                    <tr>
                        <th class="col-no">№</th>
                        <th>{$invoice_lang->sviat__invoice_payment__article|escape}</th>
                        <th>{$invoice_lang->sviat__invoice_payment__product_name|escape}</th>
                        <th class="col-qty">{$invoice_lang->sviat__invoice_payment__qty|escape}</th>
                        <th class="col-unit">{$invoice_lang->sviat__invoice_payment__unit|escape}</th>
                        <th class="col-price">{$invoice_lang->sviat__invoice_payment__price_grn|escape}</th>
                        <th class="col-sum">{$invoice_lang->sviat__invoice_payment__sum_grn|escape}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $purchases as $purchase name=goods}
                    <tr>
                        <td class="col-no">{$smarty.foreach.goods.iteration}</td>
                        <td>{$purchase->sku|default:'—'|escape}</td>
                        <td>{$purchase->product_name|escape}{if $purchase->variant_name} ({$purchase->variant_name|escape}){/if}</td>
                        <td class="col-qty">{$purchase->amount|escape}</td>
                        <td class="col-unit">{$purchase->units|default:$invoice_lang->sviat__invoice_payment__unit_pcs|escape}</td>
                        <td class="col-price">{$purchase->price_formatted|escape}</td>
                        <td class="col-sum">{$purchase->sum_formatted|escape}</td>
                    </tr>
                    {/foreach}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="border: none; padding-top: 8px;"></td>
                        <td class="col-unit" style="padding-top: 8px;">{$invoice_lang->sviat__invoice_payment__total|escape}:</td>
                        <td colspan="2" class="col-sum" style="padding-top: 8px;">{$order_total_formatted|escape} {$invoice_lang->sviat__invoice_payment__grn|escape}</td>
                    </tr>
                </tfoot>
            </table>
            <p class="invoice__total-items">{$invoice_lang->sviat__invoice_payment__total_items|escape}: {$purchases|@count}, {$invoice_lang->sviat__invoice_payment__on_sum|escape} <strong>{$order_total_formatted|escape} {$invoice_lang->sviat__invoice_payment__grn|escape}</strong></p>
            {if $total_amount_words}
            <p class="invoice__amount-words">{$total_amount_words|escape}</p>
            {/if}
            {if $invoice_settings->place_of_compilation}
            <p class="invoice__place">{$invoice_lang->sviat__invoice_payment__place_of_compilation|escape}: {$invoice_settings->place_of_compilation|escape}</p>
            {/if}
        </section>

        <div class="invoice__signatures">
            <div class="invoice__signatures-row">
                <div class="invoice__signature-block">
                    <div class="invoice__signature-label">{$invoice_lang->sviat__invoice_payment__from_supplier|escape}</div>
                    <div class="invoice__signature-line"></div>
                    <div class="invoice__signature-name">{if $invoice_settings->supplier_signature_name}({$invoice_settings->supplier_signature_name|escape}){/if}</div>
                    <p class="invoice__signature-note">* {$invoice_lang->sviat__invoice_payment__responsible_note|escape}</p>
                </div>
                <div class="invoice__signature-block">
                    <div class="invoice__signature-label">{$invoice_lang->sviat__invoice_payment__received_by|escape}</div>
                    <div class="invoice__signature-line"></div>
                    <p class="invoice__signature-proxy">{$invoice_lang->sviat__invoice_payment__by_proxy_prefix|escape}<span class="invoice__signature-proxy-gap"></span>{$invoice_lang->sviat__invoice_payment__by_proxy_no|escape}<span class="invoice__signature-proxy-gap invoice__signature-proxy-gap--wide"></span>{$invoice_lang->sviat__invoice_payment__by_proxy_from|escape}<span class="invoice__signature-proxy-gap invoice__signature-proxy-gap--wide"></span></p>
                </div>
            </div>
        </div>
    </div>

    {if $auto_print}<script>window.print();</script>{/if}
</body>
</html>
