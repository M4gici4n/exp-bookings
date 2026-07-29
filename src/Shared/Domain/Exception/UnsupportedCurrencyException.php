<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class UnsupportedCurrencyException extends DomainException
{
    public static function withCode(string $code): self
    {
        return new self("Unsupported currency code: {$code}");
    }
}
