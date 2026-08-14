<?php

namespace Modules\Sviat\InvoicePayment;

use Okay\Modules\Sviat\InvoicePayment\Controllers\InvoicePrintController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Сума прописом і дата в рахунку на оплату. Це юридичний документ, який
 * контрагент віддає у свою бухгалтерію: розбіжність між цифрами й прописом —
 * підстава не прийняти рахунок.
 *
 * AbstractController не має конструктора й типізованих властивостей, тож
 * контролер будується просто через new, а приватні форматери беруться
 * рефлексією — жодних моків не потрібно.
 */
class InvoicePrintFormattersTest extends TestCase
{
    private InvoicePrintController $controller;

    protected function setUp(): void
    {
        $this->controller = new InvoicePrintController();
    }

    private function amountToWords(float|int|string $amount): string
    {
        $reflected = new \ReflectionMethod($this->controller, 'amountToWordsUa');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        return $reflected->invoke($this->controller, $amount);
    }

    private function pluralize(int $n, string $one, string $few, string $many): string
    {
        $reflected = new \ReflectionMethod($this->controller, 'pluralizeUa');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        return $reflected->invoke($this->controller, $n, '', $one, $few, $many);
    }

    private function formatDate(?string $date): string
    {
        $reflected = new \ReflectionMethod($this->controller, 'formatInvoiceDateUa');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        return $reflected->invoke($this->controller, $date);
    }

    // --- відмінювання -------------------------------------------------------

    /**
     * Слов'янське правило: 11–19 завжди беруть форму «багато», навіть якщо
     * закінчуються на 1–4. Класичне місце для off-by-one — 111 і 121 мусять
     * розійтись.
     */
    /** @dataProvider pluralProvider */
    #[DataProvider('pluralProvider')]
    public function testUkrainianPluralRule(int $n, string $expected): void
    {
        self::assertSame(' ' . $expected, $this->pluralize($n, 'гривня', 'гривні', 'гривень'));
    }

    public static function pluralProvider(): array
    {
        return [
            '1'   => [1, 'гривня'],
            '2'   => [2, 'гривні'],
            '4'   => [4, 'гривні'],
            '5'   => [5, 'гривень'],
            '11'  => [11, 'гривень'],
            '12'  => [12, 'гривень'],
            '14'  => [14, 'гривень'],
            '19'  => [19, 'гривень'],
            '21'  => [21, 'гривня'],
            '22'  => [22, 'гривні'],
            '100' => [100, 'гривень'],
            '101' => [101, 'гривня'],
            '111' => [111, 'гривень'],
            '121' => [121, 'гривня'],
        ];
    }

    // --- сума прописом ------------------------------------------------------

    /** @dataProvider amountProvider */
    #[DataProvider('amountProvider')]
    public function testAmountInWords(float|int $amount, string $expected): void
    {
        self::assertSame($expected, $this->amountToWords($amount));
    }

    public static function amountProvider(): array
    {
        return [
            'нуль'                    => [0, 'Нуль гривень 00 копійок'],
            'одна'                    => [1, 'одна гривня 00 копійок'],
            'дві'                     => [2, 'дві гривні 00 копійок'],
            'п\'ять'                  => [5, 'п\'ять гривень 00 копійок'],
            'блок 10-19'              => [11, 'одинадцять гривень 00 копійок'],
            'двадцять одна'           => [21, 'двадцять одна гривня 00 копійок'],
            'сотня'                   => [100, 'сто гривень 00 копійок'],
            'сто одинадцять'          => [111, 'сто одинадцять гривень 00 копійок'],
            'сто двадцять одна'       => [121, 'сто двадцять одна гривня 00 копійок'],
            'тисяча'                  => [1000, 'одна тисяча гривень 00 копійок'],
            'тисяча з копійками'      => [1234.56, 'одна тисяча двісті тридцять чотири гривні 56 копійок'],
            'майже мільйон'           => [
                999999.99,
                'дев\'ятсот дев\'яносто дев\'ять тисяч дев\'ятсот дев\'яносто дев\'ять гривень 99 копійок',
            ],
        ];
    }

    /** Копійки завжди двозначні — «5 копійок» у рахунку виглядало б як помилка. */
    public function testCentsArePaddedToTwoDigits(): void
    {
        self::assertStringEndsWith('05 копійок', $this->amountToWords(12.05));
        self::assertStringEndsWith('50 копійок', $this->amountToWords(12.5));
    }

    /**
     * Копійки рахуються через подвійне округлення, бо в PHP 8.4 множення
     * 1.005 × 100 дає 100.4999… — просте (int) з'їло б копійку.
     */
    public function testHalfKopiykaRoundsUpRatherThanBeingTruncated(): void
    {
        self::assertStringEndsWith('01 копійка', $this->amountToWords(1005.005));
    }

    /**
     * Копійки відмінюються так само, як гривні. Раніше слово було
     * захардкоджене, і в рахунку друкувалось «01 копійок».
     */
    /** @dataProvider kopiykyPluralProvider */
    #[DataProvider('kopiykyPluralProvider')]
    public function testKopiykyArePluralised(float $amount, string $expectedEnding): void
    {
        self::assertStringEndsWith($expectedEnding, $this->amountToWords($amount));
    }

    public static function kopiykyPluralProvider(): array
    {
        return [
            'одна'      => [1.01, '01 копійка'],
            'дві'       => [1.02, '02 копійки'],
            'п\'ять'    => [1.05, '05 копійок'],
            'одинадцять' => [1.11, '11 копійок'],
            'двадцять одна' => [1.21, '21 копійка'],
            'нуль'      => [1.00, '00 копійок'],
        ];
    }

    /**
     * Регресія: розряд мільйонів раніше брав жіночі форми гривні («одна»,
     * «дві»), бо таблиця одиниць була одна на всі розряди — у рахунку на
     * мільйон друкувалось «одна мільйон гривень».
     */
    /** @dataProvider millionGenderProvider */
    #[DataProvider('millionGenderProvider')]
    public function testMillionsTakeTheMasculineForm(int $amount, string $expected): void
    {
        self::assertSame($expected, $this->amountToWords($amount));
    }

    public static function millionGenderProvider(): array
    {
        return [
            'один'          => [1_000_000, 'один мільйон гривень 00 копійок'],
            'два'           => [2_000_000, 'два мільйони гривень 00 копійок'],
            'п\'ять'        => [5_000_000, 'п\'ять мільйонів гривень 00 копійок'],
            'двадцять один' => [21_000_000, 'двадцять один мільйон гривень 00 копійок'],
        ];
    }

    /** Тисяча лишається в жіночому роді — «одна тисяча», а не «один». */
    public function testThousandsKeepTheFeminineForm(): void
    {
        self::assertSame('одна тисяча гривень 00 копійок', $this->amountToWords(1000));
        self::assertSame('дві тисячі гривень 00 копійок', $this->amountToWords(2000));
    }

    /**
     * Регресія: коли гривнева частина нульова, fullNumberToWordsUa віддає
     * порожній рядок, і рядок починався з пробілу — « гривень 05 копійок».
     */
    public function testAmountUnderOneHryvniaSaysZero(): void
    {
        self::assertSame('нуль гривень 05 копійок', $this->amountToWords(0.05));
        self::assertSame('нуль гривень 99 копійок', $this->amountToWords(0.99));
    }

    // --- дата ---------------------------------------------------------------

    public function testInvoiceDateUsesGenitiveMonth(): void
    {
        self::assertSame('від 5 серпня 2025 р.', $this->formatDate('2025-08-05'));
        self::assertSame('від 1 січня 2024 р.', $this->formatDate('2024-01-01'));
        self::assertSame('від 31 грудня 2024 р.', $this->formatDate('2024-12-31'));
    }

    /** День без провідного нуля: «від 05 серпня» у документі виглядає чужорідно. */
    public function testDayHasNoLeadingZero(): void
    {
        self::assertStringContainsString('від 5 ', $this->formatDate('2025-08-05'));
    }

    public function testNumericTimestampIsAccepted(): void
    {
        self::assertSame('від 5 серпня 2025 р.', $this->formatDate((string) mktime(12, 0, 0, 8, 5, 2025)));
    }

    /**
     * Нерозпізнаний рядок віддається як є, а не перетворюється на 1970 рік —
     * у документі краще побачити сміття, ніж правдоподібну неправильну дату.
     */
    public function testUnparsableDateIsReturnedAsIs(): void
    {
        self::assertSame('не дата', $this->formatDate('не дата'));
    }

    /**
     * Рядок «0» відсікається ще перевіркою empty() — у PHP empty('0') істинне,
     * тож він не доходить до гілки «мітка ≤ 0» і віддається порожнім, а не як є.
     */
    /** @dataProvider emptyDateProvider */
    #[DataProvider('emptyDateProvider')]
    public function testEmptyDateGivesEmptyString(?string $input): void
    {
        self::assertSame('', $this->formatDate($input));
    }

    public static function emptyDateProvider(): array
    {
        return [
            'null'           => [null],
            'порожній рядок' => [''],
            'рядок «0»'      => ['0'],
        ];
    }
}
