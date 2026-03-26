<?php

namespace Okay\Modules\Sviat\InvoicePayment\Extenders;

use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Router;
use Okay\Entities\PaymentsEntity;
use Okay\Modules\Sviat\InvoicePayment\Entities\InvoicePaymentDataEntity;

class BackendExtender implements ExtensionInterface
{
    private const MODULE_NAME = 'Sviat/InvoicePayment';

    private EntityFactory $entityFactory;
    private Design $design;
    private Request $request;
    private BackendTranslations $backendTranslations;

    public function __construct(EntityFactory $entityFactory, Design $design, Request $request, BackendTranslations $backendTranslations)
    {
        $this->entityFactory = $entityFactory;
        $this->design = $design;
        $this->request = $request;
        $this->backendTranslations = $backendTranslations;
    }

    public function extendFindOrder($order, $orderId)
    {
        $paymentMethod = $this->getPaymentMethodIfInvoice($order);
        if (!$paymentMethod) {
            return $order;
        }
        $dataEntity = $this->entityFactory->get(InvoicePaymentDataEntity::class);
        $data = $dataEntity->findOne(['order_id' => (int)$order->id]);
        $this->design->assign('invoice_payment_data', $data ?: (object)['edrpou' => '', 'company_name' => '', 'paid' => 0]);
        $this->design->assign('invoice_payment_api_url', Router::generateUrl('Sviat_InvoicePayment_companyByEdrpou', [], true));
        return $order;
    }

    public function extendUpdateOrder($order)
    {
        $paymentMethod = $this->getPaymentMethodIfInvoice($order);
        if (!$paymentMethod) {
            return;
        }
        // 'string' — щоб Request не обрізав лапки/символи
        $edrpou = trim((string) $this->request->post('sviat_invoice_payment_edrpou', 'string'));
        $companyName = trim(strip_tags((string) $this->request->post('sviat_invoice_payment_company_name')));
        if ($edrpou === '') {
            $this->design->assign('message_error', $this->backendTranslations->getTranslation('sviat__invoice_payment__error_edrpou'));
            return;
        }
        if ($companyName === '') {
            $this->design->assign('message_error', $this->backendTranslations->getTranslation('sviat__invoice_payment__error_company_name'));
            return;
        }
        $paid = (bool) ($order->paid ?? 0);
        $this->saveInvoicePaymentData($order, $edrpou, $companyName, $paid);
    }

    /** Синхронізує paid у sviat__invoice_payment_data при зміні «Сплачено» в адмінці. */
    public function orderMarkedPaid($output, array $ids, $state): void
    {
        if (empty($ids)) {
            return;
        }
        $dataEntity = $this->entityFactory->get(InvoicePaymentDataEntity::class);
        foreach ($ids as $orderId) {
            $row = $dataEntity->findOne(['order_id' => (int) $orderId]);
            if ($row) {
                $dataEntity->update($row->id, ['paid' => $state ? 1 : 0]);
            }
        }
    }

    private function getPaymentMethodIfInvoice($order): ?object
    {
        if ($order === null || empty($order->payment_method_id)) {
            return null;
        }
        $paymentMethod = $this->entityFactory->get(PaymentsEntity::class)->get($order->payment_method_id);
        return ($paymentMethod && $paymentMethod->module === self::MODULE_NAME) ? $paymentMethod : null;
    }

    private function saveInvoicePaymentData($order, string $edrpou, string $companyName, bool $paid): void
    {
        $dataEntity = $this->entityFactory->get(InvoicePaymentDataEntity::class);
        $existing = $dataEntity->findOne(['order_id' => (int)$order->id]);
        $data = [
            'order_id' => (int)$order->id,
            'edrpou' => $edrpou,
            'company_name' => $companyName,
            'paid' => $paid ? 1 : 0,
        ];
        if ($existing) {
            $dataEntity->update($existing->id, $data);
        } else {
            $dataEntity->add($data);
        }
    }
}
