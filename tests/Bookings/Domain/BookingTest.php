<?php declare(strict_types=1);

namespace App\Tests\Bookings\Domain;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Event\BookingCancelled;
use App\Bookings\Domain\Event\BookingConfirmed;
use App\Bookings\Domain\Exception\BookingAlreadyCancelledException;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Bookings\Domain\ValueObject\UserId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class BookingTest extends TestCase
{
    private const BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const SPOTS = 3;
    private const TOTAL_AMOUNT = 7500;

    public function testItConfirmsABooking(): void
    {
        $booking = $this->confirmBooking();

        self::assertSame(self::BOOKING_ID, $booking->id()->value());
        self::assertSame(self::SESSION_ID, $booking->sessionId()->value());
        self::assertSame(self::USER_ID, $booking->userId()->value());
        self::assertSame(self::SPOTS, $booking->spots());
        self::assertTrue($booking->totalPrice()->equals(Money::of(self::TOTAL_AMOUNT, Currency::EUR)));
        self::assertSame(BookingStatus::CONFIRMED, $booking->status());
    }

    public function testItRecordsABookingConfirmedEvent(): void
    {
        $booking = $this->confirmBooking();

        $events = $booking->pullEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(BookingConfirmed::class, $events[0]);
        self::assertTrue($events[0]->bookingId()->equals(BookingId::of(self::BOOKING_ID)));
        self::assertSame(self::SPOTS, $events[0]->spots());
    }

    public function testItCancelsAConfirmedBooking(): void
    {
        $booking = $this->confirmBooking();
        $booking->pullEvents();

        $booking->cancel();

        self::assertSame(BookingStatus::CANCELLED, $booking->status());
    }

    public function testItRecordsABookingCancelledEvent(): void
    {
        $booking = $this->confirmBooking();
        $booking->pullEvents();

        $booking->cancel();
        $events = $booking->pullEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(BookingCancelled::class, $events[0]);
        self::assertTrue($events[0]->sessionId()->equals(SessionId::of(self::SESSION_ID)));
        self::assertSame(self::SPOTS, $events[0]->spots());
    }

    public function testItRejectsCancellingAnAlreadyCancelledBooking(): void
    {
        $booking = $this->confirmBooking();
        $booking->cancel();

        $this->expectException(BookingAlreadyCancelledException::class);

        $booking->cancel();
    }

    private function confirmBooking(): Booking
    {
        return Booking::create(
            BookingId::of(self::BOOKING_ID),
            SessionId::of(self::SESSION_ID),
            UserId::of(self::USER_ID),
            self::SPOTS,
            Money::of(self::TOTAL_AMOUNT, Currency::EUR),
        );
    }
}
