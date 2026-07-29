<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InvalidSessionPriceException extends DomainException
{
    public static function negative(int $amount): self
    {
        return new self("Session price cannot be negative, got {$amount}.");
    }
}
