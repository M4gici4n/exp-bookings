<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\InMemory;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Exception\BookingNotFoundException;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Experiences\Domain\ValueObject\SessionId;

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

    public function getForModification(BookingId $id): Booking
    {
        return $this->get($id);
    }

    public function allBySession(SessionId $sessionId): array
    {
        $matches = [];

        foreach ($this->bookings as $booking) {
            if ($booking->sessionId()->equals($sessionId)) {
                $matches[] = $booking;
            }
        }

        return $matches;
    }
}
