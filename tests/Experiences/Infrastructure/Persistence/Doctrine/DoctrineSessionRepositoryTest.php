<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Exception\NotEnoughSeatsException;
use App\Experiences\Domain\Exception\SessionNotFoundException;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Experiences\Infrastructure\Persistence\Doctrine\DoctrineSessionRepository;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Shared\Infrastructure\Persistence\Doctrine\DoctrineTestCase;
use DateTimeImmutable;

final class DoctrineSessionRepositoryTest extends DoctrineTestCase
{
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const STARTS_AT = '2026-08-15 18:00:00';
    private const SCHEDULED_AT = '2026-07-01 12:00:00';

    private DoctrineSessionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctrineSessionRepository($this->entityManager);
    }

    public function testItPersistsAndRetrievesASession(): void
    {
        $this->persist($this->session(capacity: 8));
        $this->entityManager->clear();

        $session = $this->repository->get(SessionId::of(self::SESSION_ID));

        self::assertSame(self::SESSION_ID, $session->id()->value());
        self::assertSame(self::EXPERIENCE_ID, $session->experienceId()->value());
        self::assertSame(8, $session->maxCapacity());
        self::assertSame(8, $session->availableSeats());
        self::assertTrue($session->price()->equals(Money::of(2500, Currency::EUR)));
        self::assertSame(
            (new DateTimeImmutable(self::STARTS_AT))->getTimestamp(),
            $session->startsAt()->getTimestamp(),
        );
    }

    public function testItReservesSeatsAtomicallyWithoutOverselling(): void
    {
        $this->persist($this->session(capacity: 3));
        $this->entityManager->clear();

        $id = SessionId::of(self::SESSION_ID);

        $this->repository->reserveSeats($id, 2);
        self::assertSame(1, $this->freshAvailableSeats($id));

        // Only one seat remains: reserving two must fail and leave the count untouched.
        try {
            $this->repository->reserveSeats($id, 2);
            self::fail('Expected NotEnoughSeatsException.');
        } catch (NotEnoughSeatsException) {
            self::assertSame(1, $this->freshAvailableSeats($id));
        }

        $this->repository->reserveSeats($id, 1);
        self::assertSame(0, $this->freshAvailableSeats($id));

        $this->expectException(NotEnoughSeatsException::class);
        $this->repository->reserveSeats($id, 1);
    }

    public function testItReleasesSeats(): void
    {
        $this->persist($this->session(capacity: 5));
        $this->entityManager->clear();

        $id = SessionId::of(self::SESSION_ID);
        $this->repository->reserveSeats($id, 4);
        $this->repository->releaseSeats($id, 3);

        self::assertSame(4, $this->freshAvailableSeats($id));
    }

    public function testReserveSeatsThrowsWhenSessionDoesNotExist(): void
    {
        $this->expectException(SessionNotFoundException::class);

        $this->repository->reserveSeats(SessionId::of(self::SESSION_ID), 1);
    }

    private function persist(Session $session): void
    {
        $this->repository->save($session);
        $this->entityManager->flush();
    }

    private function freshAvailableSeats(SessionId $id): int
    {
        $this->entityManager->clear();

        return $this->repository->get($id)->availableSeats();
    }

    private function session(int $capacity = 10): Session
    {
        return Session::schedule(
            SessionId::of(self::SESSION_ID),
            ExperienceId::of(self::EXPERIENCE_ID),
            new DateTimeImmutable(self::STARTS_AT),
            $capacity,
            Money::of(2500, Currency::EUR),
            new DateTimeImmutable(self::SCHEDULED_AT),
        );
    }
}
