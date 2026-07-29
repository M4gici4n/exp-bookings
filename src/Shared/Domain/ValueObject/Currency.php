<?php declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\UnsupportedCurrencyException;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case JPY = 'JPY';

    public static function fromCode(string $code): self
    {
        return self::tryFrom(strtoupper($code))
            ?? throw UnsupportedCurrencyException::withCode($code);
    }

    public function decimals(): int
    {
        return match ($this) {
            self::EUR, self::USD, self::GBP => 2,
            self::JPY => 0,
        };
    }
}
