<?php declare(strict_types=1);

namespace App\Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testItCreatesMoneyFromAmountAndCurrency(): void
    {
        $money = Money::of(250, Currency::EUR);

        self::assertSame(250, $money->amount());
        self::assertSame(Currency::EUR, $money->currency());
    }

    public function testItMultipliesByAPositiveFactor(): void
    {
        $total = Money::of(250, Currency::EUR)->multiply(3);

        self::assertTrue($total->equals(Money::of(750, Currency::EUR)));
    }

    public function testItAllowsMultiplyingByZero(): void
    {
        $total = Money::of(250, Currency::EUR)->multiply(0);

        self::assertSame(0, $total->amount());
    }

    public function testItAllowsMultiplyingByANegativeFactor(): void
    {
        $result = Money::of(250, Currency::EUR)->multiply(-2);

        self::assertTrue($result->equals(Money::of(-500, Currency::EUR)));
    }

    public function testEqualsIsTrueForSameAmountAndCurrency(): void
    {
        self::assertTrue(Money::of(250, Currency::EUR)->equals(Money::of(250, Currency::EUR)));
    }

    public function testEqualsIsFalseForDifferentCurrency(): void
    {
        self::assertFalse(Money::of(250, Currency::EUR)->equals(Money::of(250, Currency::USD)));
    }

    public function testEqualsIsFalseForDifferentAmount(): void
    {
        self::assertFalse(Money::of(250, Currency::EUR)->equals(Money::of(500, Currency::EUR)));
    }
}
