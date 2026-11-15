<?php

namespace Modules\Sviat\InvoicePayment;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Request;
use Okay\Entities\PaymentsEntity;
use Okay\Helpers\ValidateHelper;
use Okay\Modules\Sviat\InvoicePayment\Extenders\FrontExtender;
use Okay\Modules\Sviat\InvoicePayment\Init\Init;
use PHPUnit\Framework\TestCase;

/**
 * Оплата за рахунком вимагає ЄДРПОУ й назву компанії. Помилку ядро бере з ланцюжка
 * ValidateHelper::getCartValidateError, тож це має бути ланка ланцюжка: результат ланки черги
 * ядро відкидає, і замовлення без реквізитів проходило.
 */
class CartValidateErrorTest extends TestCase
{
    private const int INVOICE = 26;
    private const int OTHER = 18;

    public function testCartValidationIsAChainLink(): void
    {
        [$chains, $queues] = $this->registrations();
        $link = [ValidateHelper::class, 'getCartValidateError', FrontExtender::class, 'extendGetCartValidateError'];

        $this->assertContains($link, $chains);
        $this->assertNotContains($link, $queues);
    }

    public function testMissingEdrpouIsAnError(): void
    {
        $this->assertSame('sviat__invoice_payment__error_edrpou', $this->extender(['payment_invoice_company_name' => 'ТОВ Тест'])->extendGetCartValidateError(null, $this->order()));
    }

    public function testMissingCompanyNameIsAnError(): void
    {
        $this->assertSame('sviat__invoice_payment__error_company_name', $this->extender(['payment_invoice_edrpou' => '12345678'])->extendGetCartValidateError(null, $this->order()));
    }

    /** Ланка контактів раніше в ланцюжку може зняти свою помилку і як ''. */
    public function testClearedAsEmptyStringIsChecked(): void
    {
        $this->assertSame('sviat__invoice_payment__error_edrpou', $this->extender([])->extendGetCartValidateError('', $this->order()));
    }

    public function testEarlierErrorIsKept(): void
    {
        $this->assertSame('empty_phone', $this->extender([])->extendGetCartValidateError('empty_phone', $this->order()));
    }

    /** Без помилки ланка віддає null: ланки після неї вважають «помилки немає» лише null. */
    public function testCompleteDetailsPassAsNull(): void
    {
        $post = ['payment_invoice_edrpou' => '12345678', 'payment_invoice_company_name' => 'ТОВ Тест'];

        $this->assertNull($this->extender($post)->extendGetCartValidateError(null, $this->order()));
        $this->assertNull($this->extender($post)->extendGetCartValidateError('', $this->order()));
    }

    public function testControlOtherPaymentMethodNeedsNoDetails(): void
    {
        $this->assertNull($this->extender([])->extendGetCartValidateError(null, $this->order(self::OTHER)));
    }

    /** Порожня помилка означала б, що перевірку пройдено, тож тема без перекладу однаково не пропускає замовлення. */
    public function testErrorSurvivesAThemeWithoutTheTranslation(): void
    {
        $error = $this->extender([], translated: false)->extendGetCartValidateError(null, $this->order());

        $this->assertNotNull($error);
        $this->assertNotSame('', $error);
    }

    private function order(int $paymentMethodId = self::INVOICE): object
    {
        return (object)['id' => null, 'payment_method_id' => $paymentMethodId];
    }

    private function extender(array $post, bool $translated = true): FrontExtender
    {
        $payments = $this->createStub(PaymentsEntity::class);
        $payments->method('get')->willReturnCallback(static fn($id) => (object)[
            'id' => (int)$id,
            'module' => (int)$id === self::INVOICE ? 'Sviat/InvoicePayment' : 'null',
        ]);
        $entities = $this->createStub(EntityFactory::class);
        $entities->method('get')->willReturn($payments);

        $request = new class ($post) extends Request {
            public function __construct(private readonly array $values) {}
            public function post($name = null, $type = null, $default = null) { return $this->values[$name] ?? null; }
        };
        $translations = new class ($translated) extends FrontTranslations {
            public function __construct(private readonly bool $translated) {}
            public function getTranslation($var) { return $this->translated ? $var : ''; }
        };

        return new FrontExtender($entities, $request, $translations, $this->createStub(Design::class));
    }

    /** @return array{0: list<array{string, string, string, string}>, 1: list<array{string, string, string, string}>} */
    private function registrations(): array
    {
        $init = new \ReflectionClass(RecordingInit::class)->newInstanceWithoutConstructor();
        $init->init();

        return [$init->chains, $init->queues];
    }
}

/** Init модуля без ядра: усе, що init() реєструє, лише записується. */
class RecordingInit extends Init
{
    public array $chains = [];
    public array $queues = [];

    protected function registerChainExtension($expandable, $extension)
    {
        $this->chains[] = [...$expandable, ...$extension];
    }

    protected function registerQueueExtension($expandable, $extension)
    {
        $this->queues[] = [...$expandable, ...$extension];
    }

    protected function addPermission($permission) {}
    protected function registerBackendController($controllerClass) {}
    protected function addBackendControllerPermission($controllerClass, $permission) {}
    protected function addFrontBlock($blockName, $blockTplFile, $callback = null) {}
    protected function addBackendBlock($blockName, $blockTplFile, $callback = null) {}
}
