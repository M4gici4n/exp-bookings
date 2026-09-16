<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\ReserveSpots;

final readonly class ReserveSpotsCommand
{
    public function __construct(
        public string $bookingId,
        public string $sessionId,
        public string $userId,
        public int $spots,
    ) {}
}
