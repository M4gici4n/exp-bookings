<?php declare(strict_types=1);

namespace App\Tests\Experiences\Domain;

use App\Experiences\Domain\Event\SessionScheduled;
use App\Experiences\Domain\Exception\InvalidSessionCapacityException;
use App\Experiences\Domain\Exception\NotEnoughSeatsException;
use App\Experiences\Domain\Exception\PastSessionDateException;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const NOW = '2026-07-29 12:00:00';
    private const STARTS_AT = '2026-08-15 18:00:00';
    private const MAX_CAPACITY = 10;
    private const PRICE_AMOUNT = 2500;

    public function testItSchedulesASession(): void
    {
        $session = $this->scheduleSession();

        self::assertSame(self::SESSION_ID, $session->id()->value());
        self::assertSame(self::EXPERIENCE_ID, $session->experienceId()->value());
        self::assertEquals(new DateTimeImmutable(self::STARTS_AT), $session->startsAt());
        self::assertSame(self::MAX_CAPACITY, $session->maxCapacity());
        self::assertSame(self::MAX_CAPACITY, $session->availableSeats());
        self::assertTrue($session->price()->equals(Money::of(self::PRICE_AMOUNT, Currency::EUR)));
    }

    public function testItRecordsASessionScheduledEvent(): void
    {
        $session = $this->scheduleSession();

        $events = $session->pullEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(SessionScheduled::class, $events[0]);
        self::assertTrue($events[0]->sessionId()->equals(SessionId::of(self::SESSION_ID)));
        self::assertTrue($events[0]->experienceId()->equals(ExperienceId::of(self::EXPERIENCE_ID)));
    }

    public function testItRejectsAPastDate(): void
    {
        $this->expectException(PastSessionDateException::class);

        $this->scheduleSession(startsAt: new DateTimeImmutable('2026-07-28 10:00:00'));
    }

    public function testItRejectsASessionStartingRightNow(): void
    {
        $this->expectException(PastSessionDateException::class);

        $this->scheduleSession(startsAt: new DateTimeImmutable(self::NOW));
    }

    public function testItRejectsANonPositiveCapacity(): void
    {
        $this->expectException(InvalidSessionCapacityException::class);

        $this->scheduleSession(maxCapacity: 0);
    }

    public function testItReservesSeats(): void
    {
        $session = $this->scheduleSession();

        $session->reserve(3);

        self::assertSame(self::MAX_CAPACITY - 3, $session->availableSeats());
    }

    public function testItRejectsReservingMoreSeatsThanAvailable(): void
    {
        $this->expectException(NotEnoughSeatsException::class);

        $this->scheduleSession()->reserve(self::MAX_CAPACITY + 1);
    }

    public function testItReleasesSeatsBackToTheSession(): void
    {
        $session = $this->scheduleSession();
        $session->reserve(4);

        $session->release(4);

        self::assertSame(self::MAX_CAPACITY, $session->availableSeats());
    }

    public function testItRejectsReleasingBeyondCapacity(): void
    {
        $this->expectException(LogicException::class);

        $this->scheduleSession()->release(1);
    }

    public function testItComputesTheTotalPriceForSeats(): void
    {
        $session = $this->scheduleSession();

        $total = $session->priceFor(3);

        self::assertTrue($total->equals(Money::of(self::PRICE_AMOUNT * 3, Currency::EUR)));
    }

    public function testItKnowsWhenItHasNotStartedYet(): void
    {
        $session = $this->scheduleSession();

        self::assertFalse($session->hasStarted(new DateTimeImmutable(self::NOW)));
    }

    public function testItKnowsWhenItHasAlreadyStarted(): void
    {
        $session = $this->scheduleSession();

        self::assertTrue($session->hasStarted(new DateTimeImmutable('2026-08-15 18:00:00')));
        self::assertTrue($session->hasStarted(new DateTimeImmutable('2026-08-16 00:00:00')));
    }

    private function scheduleSession(
        int $maxCapacity = self::MAX_CAPACITY,
        ?DateTimeImmutable $startsAt = null,
        ?DateTimeImmutable $now = null,
    ): Session {
        return Session::schedule(
            SessionId::of(self::SESSION_ID),
            ExperienceId::of(self::EXPERIENCE_ID),
            $startsAt ?? new DateTimeImmutable(self::STARTS_AT),
            $maxCapacity,
            Money::of(self::PRICE_AMOUNT, Currency::EUR),
            $now ?? new DateTimeImmutable(self::NOW),
        );
    }
}
