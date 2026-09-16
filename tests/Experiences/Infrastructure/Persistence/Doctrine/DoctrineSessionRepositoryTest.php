<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Exception\NotEnoughSpotsException;
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
        self::assertSame(8, $session->availableSpots());
        self::assertTrue($session->price()->equals(Money::of(2500, Currency::EUR)));
        self::assertSame(
            (new DateTimeImmutable(self::STARTS_AT))->getTimestamp(),
            $session->startsAt()->getTimestamp(),
        );
    }

    public function testItRetrievesASessionForModification(): void
    {
        $this->persist($this->session(capacity: 8));
        $this->entityManager->clear();

        $session = $this->repository->getForModification(SessionId::of(self::SESSION_ID));

        self::assertSame(self::SESSION_ID, $session->id()->value());
        self::assertSame(8, $session->availableSpots());
    }

    public function testItReservesSpotsThroughTheAggregate(): void
    {
        $this->persist($this->session(capacity: 3));
        $this->entityManager->clear();

        $id = SessionId::of(self::SESSION_ID);
        $session = $this->repository->getForModification($id);
        $session->reserve(2);
        $this->entityManager->flush();

        self::assertSame(1, $this->freshAvailableSpots($id));
    }

    public function testItRejectsReservingMoreSpotsThanAvailableWithoutTouchingTheCount(): void
    {
        $this->persist($this->session(capacity: 3));
        $this->entityManager->clear();

        $id = SessionId::of(self::SESSION_ID);

        try {
            $this->repository->getForModification($id)->reserve(4);
            self::fail('Expected NotEnoughSpotsException.');
        } catch (NotEnoughSpotsException) {
            self::assertSame(3, $this->freshAvailableSpots($id));
        }
    }

    public function testItReleasesSpotsThroughTheAggregate(): void
    {
        $this->persist($this->session(capacity: 5));
        $this->entityManager->clear();

        $id = SessionId::of(self::SESSION_ID);
        $session = $this->repository->getForModification($id);
        $session->reserve(4);
        $session->release(3);
        $this->entityManager->flush();

        self::assertSame(4, $this->freshAvailableSpots($id));
    }

    public function testGetForModificationThrowsWhenSessionDoesNotExist(): void
    {
        $this->expectException(SessionNotFoundException::class);

        $this->repository->getForModification(SessionId::of(self::SESSION_ID));
    }

    private function persist(Session $session): void
    {
        $this->repository->save($session);
        $this->entityManager->flush();
    }

    private function freshAvailableSpots(SessionId $id): int
    {
        $this->entityManager->clear();

        return $this->repository->get($id)->availableSpots();
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
