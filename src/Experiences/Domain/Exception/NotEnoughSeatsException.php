<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;

final class NotEnoughSeatsException extends DomainException
{
    public static function requested(int $requested, int $available): self
    {
        return new self("Not enough seats available: requested {$requested}, available {$available}.");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::Conflict;
    }
}
