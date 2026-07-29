<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\InMemory;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Exception\BookingNotFoundException;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;

final class InMemoryBookingRepository implements BookingRepositoryInterface
{
    /** @var array<string, Booking> */
    private array $bookings = [];

    public function save(Booking $booking): void
    {
        $this->bookings[$booking->id()->value()] = $booking;
    }

    public function get(BookingId $id): Booking
    {
        return $this->bookings[$id->value()] ?? throw BookingNotFoundException::withId($id);
    }
}
