<?php

namespace Okay\Modules\Sviat\InvoicePayment\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;

class DescriptionAdmin extends IndexAdmin
{
    private const string SETTING_PREFIX = 'sviat__invoice_payment__';

    private const array INVOICE_SETTING_KEYS = [
        'supplier_type',
        'supplier_name',
        'supplier_bank_account',
        'supplier_bank_name',
        'supplier_address',
        'supplier_recipient_code',
        'supplier_tax_info',
        'place_of_compilation',
        'supplier_signature_name',
    ];

    public function fetch()
    {
        if ($this->request->method('post')) {
            foreach (self::INVOICE_SETTING_KEYS as $key) {
                $param = self::SETTING_PREFIX . $key;
                $this->settings->set($param, $this->request->post($param));
            }
            $this->postRedirectGet->storeMessageSuccess('saved');
            $this->postRedirectGet->redirect();
        }

        $invoiceSettings = new \stdClass();
        foreach (self::INVOICE_SETTING_KEYS as $key) {
            $param = self::SETTING_PREFIX . $key;
            $invoiceSettings->{$key} = $this->settings->get($param);
        }
        $this->design->assign('invoice_settings', $invoiceSettings);

        $this->response->setContent($this->design->fetch('description.tpl'));
    }
}
