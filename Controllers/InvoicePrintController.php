<?php

namespace Okay\Modules\Sviat\InvoicePayment\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\Money;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Helpers\OrdersHelper;
use Okay\Modules\Sviat\InvoicePayment\Entities\InvoicePaymentDataEntity;
use Okay\Modules\Sviat\InvoicePayment\Init\Init;

class InvoicePrintController extends AbstractController
{
    private const MODULE_NAME = 'Sviat/InvoicePayment';

    public function render(
        OrdersEntity $ordersEntity,
        OrdersHelper $ordersHelper,
        PaymentsEntity $paymentsEntity,
        InvoicePaymentDataEntity $invoiceDataEntity,
        Money $money,
        $url,
        $forceType = null
    ) {
        $order = $ordersEntity->get((string) $url);
        if (empty($order)) {
            return $this->response->setStatusCode(404);
        }

        $paymentMethod = $order->payment_method_id
            ? $paymentsEntity->get((int) $order->payment_method_id)
            : null;
        if (empty($paymentMethod) || $paymentMethod->module !== self::MODULE_NAME) {
            return $this->response->setStatusCode(404);
        }

        $invoiceData = $invoiceDataEntity->findOne(['order_id' => (int) $order->id]);
        if (empty($invoiceData) || (empty($invoiceData->edrpou) && empty($invoiceData->company_name))) {
            return $this->response->setStatusCode(404);
        }

        $purchases = $ordersHelper->getOrderPurchasesList((int) $order->id);
        if (!$purchases) {
            return $this->response->setStatusCode(404);
        }

        $currencyId = $this->currency->id ?? null;
        foreach ($purchases as $purchase) {
            $purchase->price_formatted = $money->convert($purchase->price, $currencyId, true, false, 2);
            $purchase->sum_formatted = $money->convert($purchase->price * $purchase->amount, $currencyId, true, false, 2);
        }
        $order_total_formatted = $money->convert($order->total_price, $currencyId, true, false, 2);

        $invoiceSettings = $this->collectInvoiceSettings();
        $totalAmountWords = $this->amountToWordsUa($order->total_price);
        $invoiceLang = $this->loadInvoiceLangUa();

        $docType = $forceType ?? $this->request->get('type');
        $isWaybill = ($docType === 'waybill' || $docType === 'delivery_note');
        $documentTitle = $isWaybill
            ? ($invoiceLang->sviat__invoice_payment__waybill_title ?? 'Видаткова накладна')
            : ($invoiceLang->sviat__invoice_payment__invoice_title ?? 'Рахунок на оплату');

        $this->design->assign('order', $order);
        $this->design->assign('purchases', $purchases);
        $this->design->assign('invoice', $invoiceData);
        $this->design->assign('invoice_settings', $invoiceSettings);
        $this->design->assign('invoice_lang', $invoiceLang);
        $this->design->assign('document_title', $documentTitle);
        $this->design->assign('total_amount_words', $totalAmountWords);
        $this->design->assign('invoice_date_formatted', $this->formatInvoiceDateUa($order->date));
        $this->design->assign('order_total_formatted', $order_total_formatted);
        $this->design->assign('currency', $this->currency);
        $this->design->assign('auto_print', (bool) $this->request->get('print'));

        $this->design->setModuleDir(Init::class);
        $html = $this->design->fetch('invoice_print.tpl');
        $this->design->rollbackTemplatesDir();
        $this->design->assign('wrapper', '');
        $this->response->setContent($html);
    }

    /** Той самий шаблон, що й рахунок, з заголовком «Видаткова накладна». */
    public function renderWaybill(
        OrdersEntity $ordersEntity,
        OrdersHelper $ordersHelper,
        PaymentsEntity $paymentsEntity,
        InvoicePaymentDataEntity $invoiceDataEntity,
        Money $money,
        $url
    ) {
        return $this->render($ordersEntity, $ordersHelper, $paymentsEntity, $invoiceDataEntity, $money, $url, 'waybill');
    }

    /** Мова рахунку — завжди UA (design/lang/ua.php). */
    private function loadInvoiceLangUa(): \stdClass
    {
        $lang = [];
        $langFile = dirname(__DIR__) . '/design/lang/ua.php';
        if (is_file($langFile)) {
            require $langFile;
        }
        return (object) $lang;
    }

    private function collectInvoiceSettings(): \stdClass
    {
        $keys = [
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
        $obj = new \stdClass();
        foreach ($keys as $key) {
            $param = 'sviat__invoice_payment__' . $key;
            $obj->{$key} = $this->settings->get($param);
        }
        return $obj;
    }

    private function formatInvoiceDateUa(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        $months = [
            1 => 'січня', 2 => 'лютого', 3 => 'березня', 4 => 'квітня', 5 => 'травня', 6 => 'червня',
            7 => 'липня', 8 => 'серпня', 9 => 'вересня', 10 => 'жовтня', 11 => 'листопада', 12 => 'грудня',
        ];
        $ts = is_numeric($date) ? (int) $date : strtotime($date);
        if ($ts <= 0) {
            return $date;
        }
        $d = (int) date('d', $ts);
        $m = (int) date('n', $ts);
        $y = date('Y', $ts);
        $month = $months[$m] ?? $m;
        return "від {$d} {$month} {$y} р.";
    }

    private function amountToWordsUa($amount): string
    {
        $amount = round((float) $amount, 2);
        $whole = (int) floor($amount);
        $cents = (int) round(($amount - $whole) * 100);

        if ($whole === 0 && $cents === 0) {
            return 'Нуль гривень 00 копійок';
        }

        $grn = $this->fullNumberToWordsUa($whole);
        $grn = $this->pluralizeUa($whole, $grn, 'гривня', 'гривні', 'гривень');
        $cop = str_pad((string) $cents, 2, '0', STR_PAD_LEFT);
        return $grn . ' ' . $cop . ' копійок';
    }

    private function fullNumberToWordsUa(int $n): string
    {
        $h = [0 => '', 1 => 'сто', 2 => 'двісті', 3 => 'триста', 4 => 'чотириста', 5 => 'п\'ятсот', 6 => 'шістсот', 7 => 'сімсот', 8 => 'вісімсот', 9 => 'дев\'ятсот'];
        $t = [0 => '', 10 => 'десять', 11 => 'одинадцять', 12 => 'дванадцять', 13 => 'тринадцять', 14 => 'чотирнадцять', 15 => 'п\'ятнадцять', 16 => 'шістнадцять', 17 => 'сімнадцять', 18 => 'вісімнадцять', 19 => 'дев\'ятнадцять', 2 => 'двадцять', 3 => 'тридцять', 4 => 'сорок', 5 => 'п\'ятдесят', 6 => 'шістдесят', 7 => 'сімдесят', 8 => 'вісімдесят', 9 => 'дев\'яносто'];
        $u = [0 => '', 1 => 'одна', 2 => 'дві', 3 => 'три', 4 => 'чотири', 5 => 'п\'ять', 6 => 'шість', 7 => 'сім', 8 => 'вісім', 9 => 'дев\'ять'];
        $seg = function (int $num) use ($h, $t, $u) {
            $out = [];
            if ($num >= 100) {
                $out[] = $h[(int) floor($num / 100)];
                $num = $num % 100;
            }
            if ($num >= 20) {
                $out[] = $t[(int) floor($num / 10)];
                $num = $num % 10;
            } elseif ($num >= 10) {
                $out[] = $t[$num];
                $num = 0;
            }
            if ($num > 0) {
                $out[] = $u[$num];
            }
            return trim(implode(' ', $out));
        };

        $parts = [];
        if ($n >= 1_000_000) {
            $mil = (int) floor($n / 1_000_000);
            $parts[] = $this->pluralizeUa($mil, $seg($mil), 'мільйон', 'мільйони', 'мільйонів');
            $n = $n % 1_000_000;
        }
        if ($n >= 1_000) {
            $thou = (int) floor($n / 1_000);
            $parts[] = $this->pluralizeUa($thou, $seg($thou), 'тисяча', 'тисячі', 'тисяч');
            $n = $n % 1_000;
        }
        if ($n > 0) {
            $parts[] = $seg($n);
        }
        return implode(' ', $parts);
    }

    private function pluralizeUa(int $n, string $numStr, string $one, string $few, string $many): string
    {
        $n = $n % 100;
        if ($n >= 11 && $n <= 19) {
            return $numStr . ' ' . $many;
        }
        $r = $n % 10;
        if ($r === 1) {
            return $numStr . ' ' . $one;
        }
        if ($r >= 2 && $r <= 4) {
            return $numStr . ' ' . $few;
        }
        return $numStr . ' ' . $many;
    }
}
