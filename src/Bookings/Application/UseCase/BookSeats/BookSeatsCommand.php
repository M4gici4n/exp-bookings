<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\BookSeats;

final readonly class BookSeatsCommand
{
    public function __construct(
        public string $bookingId,
        public string $sessionId,
        public string $userId,
        public int $seats,
    ) {}
}
