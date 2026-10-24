<?php

namespace Okay\Modules\Sviat\InvoicePayment;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\AbstractModule;
use Okay\Core\Modules\Interfaces\PaymentFormInterface;
use Okay\Core\Router;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Modules\Sviat\InvoicePayment\Entities\InvoicePaymentDataEntity;

class PaymentForm extends AbstractModule implements PaymentFormInterface
{
    private const string MODULE_NAME = 'Sviat/InvoicePayment';

    private EntityFactory $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        parent::__construct();
        $this->entityFactory = $entityFactory;
    }

    public function checkoutForm($orderId)
    {
        $order = $this->entityFactory->get(OrdersEntity::class)->get((int) $orderId);
        if (!$order || !$order->payment_method_id) {
            return '';
        }
        $paymentMethod = $this->entityFactory->get(PaymentsEntity::class)->get($order->payment_method_id);
        if (!$paymentMethod || $paymentMethod->module !== self::MODULE_NAME) {
            return '';
        }
        $dataEntity = $this->entityFactory->get(InvoicePaymentDataEntity::class);

        $data = $dataEntity->findOne(['order_id' => (int) $orderId]);
        $this->design->assign('order_id', $orderId);
        $this->design->assign('edrpou', $data ? ($data->edrpou ?? '') : '');
        $this->design->assign('company_name', $data ? ($data->company_name ?? '') : '');
        $this->design->assign('invoice_payment_api_url', Router::generateUrl('Sviat_InvoicePayment_companyByEdrpou', [], true));
        $this->design->assign('invoice_print_url', Router::generateUrl('Sviat_InvoicePayment_invoicePrint', ['url' => $order->url], true));

        return $this->design->fetch('form.tpl');
    }
}
