<?php declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case JPY = 'JPY';

    public static function fromCode(string $code): self
    {
        return self::tryFrom(strtoupper($code))
            ?? throw new InvalidArgumentException("Unsupported currency code: {$code}");
    }

    public function decimals(): int
    {
        return match ($this) {
            self::EUR, self::USD, self::GBP => 2,
            self::JPY => 0,
        };
    }
}
