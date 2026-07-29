<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Persistence\Doctrine;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Exception\BookingNotFoundException;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Bookings\Domain\ValueObject\UserId;
use App\Bookings\Infrastructure\Persistence\Doctrine\DoctrineBookingRepository;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Shared\Infrastructure\Persistence\Doctrine\DoctrineTestCase;

final class DoctrineBookingRepositoryTest extends DoctrineTestCase
{
    private const BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const SEATS = 3;
    private const TOTAL_AMOUNT = 7500;

    private DoctrineBookingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctrineBookingRepository($this->entityManager);
    }

    public function testItPersistsAndRetrievesAConfirmedBooking(): void
    {
        $this->persist($this->confirmedBooking());

        $booking = $this->repository->get(BookingId::of(self::BOOKING_ID));

        self::assertSame(self::BOOKING_ID, $booking->id()->value());
        self::assertSame(self::SESSION_ID, $booking->sessionId()->value());
        self::assertSame(self::USER_ID, $booking->userId()->value());
        self::assertSame(self::SEATS, $booking->seats());
        self::assertSame(BookingStatus::CONFIRMED, $booking->status());
        self::assertTrue($booking->totalPrice()->equals(Money::of(self::TOTAL_AMOUNT, Currency::EUR)));
    }

    public function testItPersistsACancelledBookingStatus(): void
    {
        $booking = $this->confirmedBooking();
        $booking->cancel();

        $this->persist($booking);

        self::assertSame(BookingStatus::CANCELLED, $this->repository->get(BookingId::of(self::BOOKING_ID))->status());
    }

    public function testGetThrowsWhenBookingDoesNotExist(): void
    {
        $this->expectException(BookingNotFoundException::class);

        $this->repository->get(BookingId::of(self::BOOKING_ID));
    }

    private function persist(Booking $booking): void
    {
        $this->repository->save($booking);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function confirmedBooking(): Booking
    {
        return Booking::confirm(
            BookingId::of(self::BOOKING_ID),
            SessionId::of(self::SESSION_ID),
            UserId::of(self::USER_ID),
            self::SEATS,
            Money::of(self::TOTAL_AMOUNT, Currency::EUR),
        );
    }
}
