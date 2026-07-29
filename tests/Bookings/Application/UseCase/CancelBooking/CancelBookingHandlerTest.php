<?php declare(strict_types=1);

namespace App\Tests\Bookings\Application\UseCase\CancelBooking;

use App\Bookings\Application\UseCase\CancelBooking\CancelBookingCommand;
use App\Bookings\Application\UseCase\CancelBooking\CancelBookingHandler;
use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Event\BookingCancelled;
use App\Bookings\Domain\Exception\BookingAlreadyCancelledException;
use App\Bookings\Domain\Exception\BookingNotFoundException;
use App\Bookings\Domain\Exception\LateCancellationException;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Bookings\Domain\ValueObject\UserId;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Bookings\Infrastructure\InMemory\InMemoryBookingRepository;
use App\Tests\Experiences\Infrastructure\InMemory\InMemorySessionRepository;
use App\Tests\Shared\Infrastructure\Event\InMemoryEventDispatcher;
use App\Tests\Shared\Infrastructure\Service\FixedClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CancelBookingHandlerTest extends TestCase
{
    private const BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FD2';
    private const SCHEDULED_AT = '2026-07-01 12:00:00';
    private const STARTS_AT = '2026-08-15 18:00:00';
    private const CANCEL_ALLOWED_AT = '2026-08-10 12:00:00';
    private const CANCEL_TOO_LATE_AT = '2026-08-15 00:00:00';
    private const CAPACITY = 10;
    private const SEATS = 3;

    private InMemorySessionRepository $sessions;
    private InMemoryBookingRepository $bookings;
    private InMemoryEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        $this->sessions = new InMemorySessionRepository();
        $this->bookings = new InMemoryBookingRepository();
        $this->eventDispatcher = new InMemoryEventDispatcher();
    }

    public function testItCancelsABookingAndReleasesSeats(): void
    {
        $this->givenSessionWithReservedSeats();
        $this->givenConfirmedBooking();

        ($this->handlerAt(self::CANCEL_ALLOWED_AT))(new CancelBookingCommand(self::BOOKING_ID));

        self::assertSame(BookingStatus::CANCELLED, $this->bookings->get(BookingId::of(self::BOOKING_ID))->status());
        self::assertSame(self::CAPACITY, $this->sessions->get(SessionId::of(self::SESSION_ID))->availableSeats());
    }

    public function testItDispatchesABookingCancelledEvent(): void
    {
        $this->givenSessionWithReservedSeats();
        $this->givenConfirmedBooking();

        ($this->handlerAt(self::CANCEL_ALLOWED_AT))(new CancelBookingCommand(self::BOOKING_ID));

        $events = $this->eventDispatcher->dispatchedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(BookingCancelled::class, $events[0]);
    }

    public function testItRejectsWhenBookingDoesNotExist(): void
    {
        $this->expectException(BookingNotFoundException::class);

        ($this->handlerAt(self::CANCEL_ALLOWED_AT))(new CancelBookingCommand(self::BOOKING_ID));
    }

    public function testItRejectsCancellingWithin24HoursOfTheSessionStart(): void
    {
        $this->givenSessionWithReservedSeats();
        $this->givenConfirmedBooking();

        $this->expectException(LateCancellationException::class);

        ($this->handlerAt(self::CANCEL_TOO_LATE_AT))(new CancelBookingCommand(self::BOOKING_ID));
    }

    public function testItRejectsCancellingAnAlreadyCancelledBooking(): void
    {
        $this->givenSessionWithReservedSeats();
        $this->givenConfirmedBooking(cancelled: true);

        $this->expectException(BookingAlreadyCancelledException::class);

        ($this->handlerAt(self::CANCEL_ALLOWED_AT))(new CancelBookingCommand(self::BOOKING_ID));
    }

    private function givenSessionWithReservedSeats(): void
    {
        $this->sessions->save(Session::schedule(
            SessionId::of(self::SESSION_ID),
            ExperienceId::of(self::EXPERIENCE_ID),
            new DateTimeImmutable(self::STARTS_AT),
            self::CAPACITY,
            Money::of(2500, Currency::EUR),
            new DateTimeImmutable(self::SCHEDULED_AT),
        ));

        $this->sessions->reserveSeats(SessionId::of(self::SESSION_ID), self::SEATS);
    }

    private function givenConfirmedBooking(bool $cancelled = false): void
    {
        $booking = Booking::confirm(
            BookingId::of(self::BOOKING_ID),
            SessionId::of(self::SESSION_ID),
            UserId::of(self::USER_ID),
            self::SEATS,
            Money::of(7500, Currency::EUR),
        );

        $booking->pullEvents();

        if ($cancelled) {
            $booking->cancel();
        }

        $this->bookings->save($booking);
    }

    private function handlerAt(string $now): CancelBookingHandler
    {
        return new CancelBookingHandler(
            $this->bookings,
            $this->sessions,
            new FixedClock(new DateTimeImmutable($now)),
            $this->eventDispatcher,
        );
    }
}
