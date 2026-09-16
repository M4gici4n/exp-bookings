<?php declare(strict_types=1);

namespace App\Bookings\Domain\Repository;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Experiences\Domain\ValueObject\SessionId;

interface BookingRepositoryInterface
{
    public function save(Booking $booking): void;

    public function get(BookingId $id): Booking;

    public function getForModification(BookingId $id): Booking;

    /** @return Booking[] */
    public function allBySession(SessionId $sessionId): array;
}
