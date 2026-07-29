<?php declare(strict_types=1);

namespace App\Bookings\Domain\Exception;

use App\Bookings\Domain\ValueObject\BookingId;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;

final class BookingNotFoundException extends DomainException
{
    public static function withId(BookingId $id): self
    {
        return new self("Booking {$id} not found.");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::NotFound;
    }
}
