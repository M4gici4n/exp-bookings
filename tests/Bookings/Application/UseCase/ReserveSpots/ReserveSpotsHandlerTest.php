<?php declare(strict_types=1);

namespace App\Tests\Bookings\Application\UseCase\ReserveSpots;

use App\Bookings\Application\UseCase\ReserveSpots\ReserveSpotsCommand;
use App\Bookings\Application\UseCase\ReserveSpots\ReserveSpotsHandler;
use App\Bookings\Domain\Event\BookingConfirmed;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Experiences\Domain\Exception\InvalidSpotCountException;
use App\Experiences\Domain\Exception\NotEnoughSpotsException;
use App\Experiences\Domain\Exception\SessionAlreadyStartedException;
use App\Experiences\Domain\Exception\SessionNotFoundException;
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

final class ReserveSpotsHandlerTest extends TestCase
{
    private const BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FD2';
    private const NOW = '2026-07-29 12:00:00';
    private const STARTS_AT = '2026-08-15 18:00:00';
    private const CAPACITY = 10;
    private const PRICE_AMOUNT = 2500;

    private InMemorySessionRepository $sessions;
    private InMemoryBookingRepository $bookings;
    private InMemoryEventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        $this->sessions = new InMemorySessionRepository();
        $this->bookings = new InMemoryBookingRepository();
        $this->eventDispatcher = new InMemoryEventDispatcher();
    }

    public function testItBooksSpots(): void
    {
        $this->givenScheduledSession();

        ($this->handlerAt(self::NOW))($this->command(spots: 3));

        $booking = $this->bookings->get(BookingId::of(self::BOOKING_ID));
        self::assertSame(self::SESSION_ID, $booking->sessionId()->value());
        self::assertSame(self::USER_ID, $booking->userId()->value());
        self::assertSame(3, $booking->spots());
        self::assertSame(BookingStatus::CONFIRMED, $booking->status());
        self::assertTrue($booking->totalPrice()->equals(Money::of(7500, Currency::EUR)));

        self::assertSame(self::CAPACITY - 3, $this->sessions->get(SessionId::of(self::SESSION_ID))->availableSpots());
    }

    public function testItDispatchesABookingConfirmedEvent(): void
    {
        $this->givenScheduledSession();

        ($this->handlerAt(self::NOW))($this->command(spots: 3));

        $events = $this->eventDispatcher->dispatchedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(BookingConfirmed::class, $events[0]);
    }

    public function testItRejectsWhenSessionDoesNotExist(): void
    {
        $this->expectException(SessionNotFoundException::class);

        ($this->handlerAt(self::NOW))($this->command());
    }

    public function testItRejectsBookingAStartedSession(): void
    {
        $this->givenScheduledSession();

        $this->expectException(SessionAlreadyStartedException::class);

        ($this->handlerAt(self::STARTS_AT))($this->command());
    }

    public function testItRejectsWhenThereAreNotEnoughSpots(): void
    {
        $this->givenScheduledSession();

        $this->expectException(NotEnoughSpotsException::class);

        ($this->handlerAt(self::NOW))($this->command(spots: self::CAPACITY + 1));
    }

    public function testItRejectsBookingZeroSpots(): void
    {
        $this->givenScheduledSession();

        $this->expectException(InvalidSpotCountException::class);

        ($this->handlerAt(self::NOW))($this->command(spots: 0));
    }

    public function testItRejectsBookingNegativeSpots(): void
    {
        $this->givenScheduledSession();

        $this->expectException(InvalidSpotCountException::class);

        ($this->handlerAt(self::NOW))($this->command(spots: -5));
    }

    private function givenScheduledSession(): void
    {
        $this->sessions->save(Session::schedule(
            SessionId::of(self::SESSION_ID),
            ExperienceId::of(self::EXPERIENCE_ID),
            new DateTimeImmutable(self::STARTS_AT),
            self::CAPACITY,
            Money::of(self::PRICE_AMOUNT, Currency::EUR),
            new DateTimeImmutable(self::NOW),
        ));
    }

    private function handlerAt(string $now): ReserveSpotsHandler
    {
        return new ReserveSpotsHandler(
            $this->sessions,
            $this->bookings,
            new FixedClock(new DateTimeImmutable($now)),
            $this->eventDispatcher,
        );
    }

    private function command(int $spots = 2): ReserveSpotsCommand
    {
        return new ReserveSpotsCommand(self::BOOKING_ID, self::SESSION_ID, self::USER_ID, $spots);
    }
}
