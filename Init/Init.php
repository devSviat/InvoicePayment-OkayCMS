<?php

namespace Okay\Modules\Sviat\InvoicePayment\Init;

use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Helpers\OrdersHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Modules\Sviat\InvoicePayment\Entities\InvoicePaymentDataEntity;
use Okay\Modules\Sviat\InvoicePayment\Extenders\BackendExtender;
use Okay\Modules\Sviat\InvoicePayment\Extenders\FrontExtender;
use Okay\Controllers\OrderController;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Entities\OrdersEntity;

class Init extends AbstractInit
{
    public function install()
    {
        $this->setModuleType(MODULE_TYPE_PAYMENT);
        $this->setBackendMainController('DescriptionAdmin');

        $this->migrateEntityTable(InvoicePaymentDataEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('order_id'))->setTypeInt(11)->setIndexUnique(),
            (new EntityField('edrpou'))->setTypeVarchar(20),
            (new EntityField('company_name'))->setTypeVarchar(500),
            (new EntityField('paid'))->setTypeTinyInt(1, true),
        ]);
    }

    public function init()
    {
        $this->addPermission('sviat__invoice_payment');

        $this->registerBackendController('DescriptionAdmin');
        $this->addBackendControllerPermission('DescriptionAdmin', 'sviat__invoice_payment');

        $this->addFrontBlock('front_cart_payment', 'cart_payment_fields.tpl');
        $this->addFrontBlock('front_scripts_after_validate', 'invoice_payment_edrpou.js');
        $this->addFrontBlock('front_scripts_after_validate', 'validation.js');
        $this->addFrontBlock('order_details_extra', 'order_invoice_payment_details.tpl');

        $this->registerQueueExtension(
            [OrdersHelper::class, 'finalCreateOrderProcedure'],
            [FrontExtender::class, 'extendFinalCreateOrderProcedure']
        );

        $this->registerQueueExtension(
            [ValidateHelper::class, 'getCartValidateError'],
            [FrontExtender::class, 'extendGetCartValidateError']
        );

        $this->registerQueueExtension(
            [OrdersHelper::class, 'getOrderPurchasesList'],
            [FrontExtender::class, 'extendGetOrderPurchasesList']
        );

        $this->registerQueueExtension(
            [OrderController::class, 'render'],
            [FrontExtender::class, 'extendOrderControllerRender']
        );

        $this->registerQueueExtension(
            [BackendOrdersHelper::class, 'findOrder'],
            [BackendExtender::class, 'extendFindOrder']
        );

        $this->registerQueueExtension(
            [BackendOrdersHelper::class, 'update'],
            [BackendExtender::class, 'extendUpdateOrder']
        );

        $this->registerQueueExtension(
            [OrdersEntity::class, 'markedPaid'],
            [BackendExtender::class, 'orderMarkedPaid']
        );

        $this->addBackendBlock('order_payment_info', 'order_invoice_print_btn.tpl');
    }
}
