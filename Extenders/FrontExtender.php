<?php

namespace Okay\Modules\Sviat\InvoicePayment\Extenders;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Modules\Sviat\InvoicePayment\Entities\InvoicePaymentDataEntity;

class FrontExtender implements ExtensionInterface
{
    private const MODULE_NAME = 'Sviat/InvoicePayment';

    private EntityFactory $entityFactory;
    private Request $request;
    private FrontTranslations $frontTranslations;
    private Design $design;

    public function __construct(EntityFactory $entityFactory, Request $request, FrontTranslations $frontTranslations, Design $design)
    {
        $this->entityFactory = $entityFactory;
        $this->request = $request;
        $this->frontTranslations = $frontTranslations;
        $this->design = $design;
    }

    public function extendGetOrderPurchasesList($result, $orderId)
    {
        if (!$result || !$orderId) {
            return $result;
        }
        $order = $this->entityFactory->get(OrdersEntity::class)->get((int)$orderId);
        $paymentMethod = $this->getPaymentMethodIfInvoice($order);
        if (!$paymentMethod) {
            return $result;
        }
        $data = $this->entityFactory->get(InvoicePaymentDataEntity::class)
            ->findOne(['order_id' => (int)$orderId]);
        if ($data && ($data->edrpou || $data->company_name)) {
            $this->design->assign('invoice_payment_data', $data);
        }
        return $result;
    }

    public function extendFinalCreateOrderProcedure($result, $order)
    {
        if (!$this->getPaymentMethodIfInvoice($order)) {
            return;
        }
        $data = $this->getPostInvoiceData();
        if ($data['edrpou'] === '' && $data['company_name'] === '') {
            return;
        }
        $this->saveInvoicePaymentData((int) $order->id, $data['edrpou'], $data['company_name']);
    }

    public function extendGetCartValidateError($error, $order)
    {
        if ($error || !$this->getPaymentMethodIfInvoice($order)) {
            return $error;
        }
        $data = $this->getPostInvoiceData();
        if ($data['edrpou'] === '') {
            return $this->frontTranslations->getTranslation('sviat__invoice_payment__error_edrpou');
        }
        if ($data['company_name'] === '') {
            return $this->frontTranslations->getTranslation('sviat__invoice_payment__error_company_name');
        }
        return $error;
    }

    public function extendOrderControllerRender($result, ...$args)
    {
        if (!$this->request->post('save_invoice_payment')) {
            return $result;
        }
        $orderId = (int) $this->request->post('order_id', 'integer');
        if (!$orderId) {
            return $result;
        }
        $order = $this->entityFactory->get(OrdersEntity::class)->get($orderId);
        if (!$this->getPaymentMethodIfInvoice($order)) {
            return $result;
        }
        $data = $this->getPostInvoiceData();
        $this->saveInvoicePaymentData($order->id, $data['edrpou'], $data['company_name']);
        Response::redirectTo(Router::generateUrl('order', ['url' => $order->url], true));
    }

    private function getPostInvoiceData(): array
    {
        return [
            'edrpou' => trim((string) $this->request->post('payment_invoice_edrpou', 'string')),
            'company_name' => trim(strip_tags((string) $this->request->post('payment_invoice_company_name'))),
        ];
    }

    private function getPaymentMethodIfInvoice($order): ?object
    {
        if ($order === null || empty($order->payment_method_id)) {
            return null;
        }
        $paymentMethod = $this->entityFactory->get(PaymentsEntity::class)->get($order->payment_method_id);
        return ($paymentMethod && $paymentMethod->module === self::MODULE_NAME) ? $paymentMethod : null;
    }

    private function saveInvoicePaymentData(int $orderId, string $edrpou, string $companyName): void
    {
        $dataEntity = $this->entityFactory->get(InvoicePaymentDataEntity::class);
        $existing = $dataEntity->findOne(['order_id' => $orderId]);
        $data = [
            'order_id' => $orderId,
            'edrpou' => $edrpou,
            'company_name' => $companyName,
        ];
        if ($existing) {
            $dataEntity->update($existing->id, $data);
        } else {
            $dataEntity->add($data);
        }
    }
}
