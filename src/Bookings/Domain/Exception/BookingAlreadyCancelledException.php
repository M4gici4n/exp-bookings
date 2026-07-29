<?php declare(strict_types=1);

namespace App\Bookings\Domain\Exception;

use App\Bookings\Domain\ValueObject\BookingId;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;

final class BookingAlreadyCancelledException extends DomainException
{
    public static function withId(BookingId $id): self
    {
        return new self("Booking {$id} is already cancelled.");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::Conflict;
    }
}
