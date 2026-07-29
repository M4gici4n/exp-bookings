<?php declare(strict_types=1);

namespace App\Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\UnsupportedCurrencyException;
use App\Shared\Domain\ValueObject\Currency;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    public function testTwoDecimalCurrenciesReportTwoDecimals(): void
    {
        self::assertSame(2, Currency::EUR->decimals());
        self::assertSame(2, Currency::USD->decimals());
        self::assertSame(2, Currency::GBP->decimals());
    }

    public function testZeroDecimalCurrencyReportsZeroDecimals(): void
    {
        self::assertSame(0, Currency::JPY->decimals());
    }

    public function testItIsBuiltFromItsIsoCode(): void
    {
        self::assertSame(Currency::EUR, Currency::fromCode('EUR'));
    }

    public function testItNormalizesTheCodeToUppercase(): void
    {
        self::assertSame(Currency::EUR, Currency::fromCode('eur'));
    }

    public function testItRejectsAnUnsupportedCurrencyCode(): void
    {
        $this->expectException(UnsupportedCurrencyException::class);

        Currency::fromCode('XXX');
    }
}
