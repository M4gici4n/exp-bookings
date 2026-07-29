<?php declare(strict_types=1);

namespace App\Bookings\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;
use DateTimeImmutable;

final class LateCancellationException extends DomainException
{
    public static function within24HoursOf(DateTimeImmutable $startsAt): self
    {
        return new self(
            "Cannot cancel a booking within 24 hours of the session start: {$startsAt->format(DateTimeImmutable::ATOM)}."
        );
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::InvalidArgument;
    }
}
