<?php

namespace PhpOffice\PhpSpreadsheetTests\Helper\NumberFormat;

use PhpOffice\PhpSpreadsheet\Helper\NumberFormat\Accounting;
use PhpOffice\PhpSpreadsheet\Helper\NumberFormat\Currency;
use PhpOffice\PhpSpreadsheet\Helper\NumberFormat\Number;
use PHPUnit\Framework\TestCase;

class AccountingWithIntlTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('intl')) {
            self::markTestSkipped('The Intl extension is not available');
        }

        if (PHP_VERSION < '70400') {
            self::markTestSkipped('The Intl extension does not support Accounting below PHP 7.4');
        }

        if (Number::icuVersion() < 53.0) {
            self::markTestSkipped('The Intl extension does not support Accounting Formats without ICU 53');
        }
    }

    /**
     * @group intl
     * @dataProvider accountingMaskWithIntlData
     */
    public function testAccountingMaskWithIntl(string $expectedResult, ...$args): void
    {
        $currencyFormatter = new Accounting(...$args);
        $currencyFormatMask = $currencyFormatter->format();
        self::assertSame($expectedResult, $currencyFormatMask);
    }

    public function accountingMaskWithIntlData()
    {
        return [
            'Default' => [PHP_VERSION >= 80000 ? '[$$-en-US]#,##0.00': '[$$-en-US]#,##0.00;([$$-en-US]#,##0.00)'],
            'English, US (Default)' => [PHP_VERSION >= 80000 ? '[$$-en-US]#,##0.00' : '[$$-en-US]#,##0.00;([$$-en-US]#,##0.00)', 'en_US'],
            'English, UK/GB' => [PHP_VERSION >= 80000 ? '[$£-en-GB]#,##0.00' : '[$£-en-GB]#,##0.00;([$£-en-GB]#,##0.00)', 'en_GB'],
            'English, IE, Euros' => [PHP_VERSION >= 80000 ? '[$€-en-IE]#,##0.00' : '[$€-en-IE]#,##0.00;([$€-en-IE]#,##0.00)', 'en_IE', 'EUR'],
            'Dutch, Netherlands' => [PHP_VERSION >= 80000 ? '[$€-nl-NL] #,##0.00' : '[$€-nl-NL] #,##0.00;([$€-nl-NL] #,##0.00)', 'nl_NL'],
            'West Frisian, Netherlands' => [PHP_VERSION >= 80000 ? '[$€-fy-NL] #,##0.00' : '[$€-fy-NL] #,##0.00;([$€-fy-NL] #,##0.00)', 'fy_NL'],
            'Spanish, Spain' => ['#,##0.00 [$€-es-ES]', 'es_ES'],
            'English, Canada' => [PHP_VERSION >= 80000 ? '[$CA$-en-CA]#,##0.00' : '[$CA$-en-CA]#,##0.00;([$CA$-en-CA]#,##0.00)', 'en_CA'],
            'French, Canada' => [PHP_VERSION >= 80000 ? '#,##0.00 [$$CA-fr-CA]' : '#,##0.00 [$$CA-fr-CA];(#,##0.00 [$$CA-fr-CA])', 'fr_CA'],
            'English, Canada, US Dollars' => [PHP_VERSION >= 80000 ? '[$$-en-CA]#,##0.00' : '[$$-en-CA]#,##0.00;([$$-en-CA]#,##0.00)', 'en_CA', 'USD'],
            'French, Canada, US Dollars' => [PHP_VERSION >= 80000 ? '#,##0.00 [$$US-fr-CA]' : '#,##0.00 [$$US-fr-CA];(#,##0.00 [$$US-fr-CA])', 'fr_CA', 'USD'],
            'Danish, Denmark, Krone' => ['#,##0.00 [$kr.-da-DK]', 'da_DK', 'DKK'],
        ];
    }

    /**
     * @group intl
     * @dataProvider accountingMaskWithIntlDataManualOverrides
     */
    public function testAccountingMaskWithIntlManualOverrides(string $expectedResult, string $locale, array $args): void
    {
        $currencyFormatter = new Accounting($locale);

        foreach ($args as $methodName => $methodArgs) {
            $currencyFormatter->{$methodName}(...$methodArgs);
        }

        $currencyFormatMask = $currencyFormatter->format();
        self::assertSame($expectedResult, $currencyFormatMask);
    }

    public function accountingMaskWithIntlDataManualOverrides()
    {
        return [
            'Dutch Euro, 3 decimals' => [
                PHP_VERSION >= 80000 ? '[$€-nl-NL] #,##0.000' : '[$€-nl-NL] #,##0.000;([$€-nl-NL] #,##0.000)',
                'nl_NL',
                [
                    'setDecimals' => [3],
                ],
            ],
            'Dutch Euro, trailing currency symbol' => [
                PHP_VERSION >= 80000 ? '#,##0.00 [$€-nl-NL]' : '#,##0.00 [$€-nl-NL];(#,##0.00 [$€-nl-NL])',
                'nl_NL',
                [
                    'setCurrencySymbol' => ['€', Currency::CURRENCY_SYMBOL_TRAILING, Number::NON_BREAKING_SPACE],
                ],
            ],
            'Spanish Euro (Trailing currency symbol), No decimals, Negative in brackets' => [
                '#,##0 [$€-es-ES];(#,##0 [$€-es-ES])',
                'es_ES',
                [
                    'setDecimals' => [0],
                    'wrapNegativeValues' => [true],
                ],
            ],
            'Denmark, Krone, Trailing negative sign' => [
                '#,##0.00 [$kr.-da-DK];#,##0.00 - [$kr.-da-DK]',
                'da_DK',
                [
                    'trailingSign' => [true, Number::NON_BREAKING_SPACE],
                ],
            ],
            'Denmark, Krone, Trailing sign' => [
                '#,##0.00 + [$kr.-da-DK];#,##0.00 - [$kr.-da-DK];"-"?? [$kr.-da-DK]',
                'da_DK',
                [
                    'trailingSign' => [true, Number::NON_BREAKING_SPACE],
                    'displayPositiveSign' => [true],
                ],
            ],
        ];
    }
}