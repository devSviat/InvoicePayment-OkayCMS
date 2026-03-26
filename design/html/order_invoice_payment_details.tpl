{if $invoice_payment_data}
    {if $invoice_payment_data->edrpou}
    <tr>
        <td><span data-language="sviat_invoice_payment_edrpou">{$lang->sviat__invoice_payment__edrpou|escape}</span></td>
        <td>{$invoice_payment_data->edrpou|escape}</td>
    </tr>
    {/if}
    {if $invoice_payment_data->company_name}
    <tr>
        <td><span data-language="sviat_invoice_payment_company_name">{$lang->sviat__invoice_payment__company_name|escape}</span></td>
        <td>{$invoice_payment_data->company_name|escape}</td>
    </tr>
    {/if}
{/if}
