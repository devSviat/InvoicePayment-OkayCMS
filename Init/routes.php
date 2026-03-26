<?php

namespace Okay\Modules\Sviat\InvoicePayment;

return [
    'Sviat_InvoicePayment_companyByEdrpou' => [
        'slug' => 'invoice-payment/company-by-edrpou',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\CompanyByEdrpouController',
            'method' => 'getCompany',
        ],
    ],
    'Sviat_InvoicePayment_invoicePrint' => [
        'slug' => 'invoice-payment/print/{$url}',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\InvoicePrintController',
            'method' => 'render',
        ],
    ],
    'Sviat_InvoicePayment_invoiceWaybill' => [
        'slug' => 'invoice-payment/waybill/{$url}',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\InvoicePrintController',
            'method' => 'renderWaybill',
        ],
    ],
];
