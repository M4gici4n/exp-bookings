<?php declare(strict_types=1);

namespace App\Bookings\Application\Query\GetBooking;

final readonly class GetBookingQuery
{
    public function __construct(
        public string $bookingId,
    ) {}
}
