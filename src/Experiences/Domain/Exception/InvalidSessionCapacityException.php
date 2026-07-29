<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InvalidSessionCapacityException extends DomainException
{
    public static function of(int $capacity): self
    {
        return new self("Session capacity must be at least one seat, got {$capacity}.");
    }
}
