<?php declare(strict_types=1);

namespace App\Bookings\Domain\Repository;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\ValueObject\BookingId;

interface BookingRepositoryInterface
{
    public function save(Booking $booking): void;

    public function get(BookingId $id): Booking;
}
