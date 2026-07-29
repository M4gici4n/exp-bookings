<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\CancelBooking;

final readonly class CancelBookingCommand
{
    public function __construct(
        public string $bookingId,
    ) {}
}
