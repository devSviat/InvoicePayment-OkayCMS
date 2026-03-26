<?php

namespace Okay\Modules\Sviat\InvoicePayment\Entities;

use Okay\Core\Entity\Entity;

class InvoicePaymentDataEntity extends Entity
{
    protected static $table = 'sviat__invoice_payment_data';

    protected static $fields = [
        'id',
        'order_id',
        'edrpou',
        'company_name',
        'paid',
    ];
}
