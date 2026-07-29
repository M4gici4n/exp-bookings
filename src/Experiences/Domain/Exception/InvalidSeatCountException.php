<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;

final class InvalidSeatCountException extends DomainException
{
    public static function of(int $seats): self
    {
        return new self("Seats must be a positive number, got {$seats}.");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::InvalidArgument;
    }
}
