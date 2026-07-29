<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Exception\NotEnoughSeatsException;
use App\Experiences\Domain\Exception\SessionNotFoundException;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use DateTimeImmutable;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSessionRepository implements SessionRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(Session $session): void
    {
        $this->entityManager->persist($session);
    }

    public function get(SessionId $id): Session
    {
        return $this->entityManager->find(Session::class, $id)
            ?? throw SessionNotFoundException::withId($id);
    }

    public function existsForExperienceOnDay(ExperienceId $experienceId, DateTimeImmutable $day): bool
    {
        $found = $this->entityManager->getConnection()->fetchOne(
            'SELECT 1 FROM sessions WHERE experience_id = :experienceId AND DATE(starts_at) = :day LIMIT 1',
            [
                'experienceId' => $experienceId->value(),
                'day' => $day->format('Y-m-d'),
            ],
        );

        return $found !== false;
    }

    public function reserveSeats(SessionId $id, int $seats): void
    {
        // Atomic conditional decrement: the where guard prevents overselling under
        // concurrency, since the check and the update happen in a single statement.

        $affected = $this->entityManager->getConnection()->executeStatement(
            'UPDATE sessions SET available_seats = available_seats - :seats
             WHERE id = :id AND available_seats >= :seats',
            ['seats' => $seats, 'id' => $id->value()],
            ['seats' => ParameterType::INTEGER, 'id' => ParameterType::STRING],
        );

        if ($affected === 0) {
            throw $this->reservationFailure($id, $seats);
        }
    }

    public function releaseSeats(SessionId $id, int $seats): void
    {
        // Called on cancellation with the exact seats previously reserved, so it can
        // never exceed the capacity.
        // A plain atomic increment is enough.

        $this->entityManager->getConnection()->executeStatement(
            'UPDATE sessions SET available_seats = available_seats + :seats WHERE id = :id',
            ['seats' => $seats, 'id' => $id->value()],
            ['seats' => ParameterType::INTEGER, 'id' => ParameterType::STRING],
        );
    }

    private function reservationFailure(SessionId $id, int $seats): NotEnoughSeatsException|SessionNotFoundException
    {
        $available = $this->entityManager->getConnection()->fetchOne(
            'SELECT available_seats FROM sessions WHERE id = :id',
            ['id' => $id->value()],
        );

        if ($available === false) {
            return SessionNotFoundException::withId($id);
        }

        return NotEnoughSeatsException::requested($seats, (int) $available);
    }
}
