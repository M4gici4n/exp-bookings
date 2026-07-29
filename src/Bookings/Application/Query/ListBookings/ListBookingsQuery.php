<?php declare(strict_types=1);

namespace App\Bookings\Application\Query\ListBookings;

final readonly class ListBookingsQuery
{
    public function __construct(
        public string $sessionId,
    ) {}
}
