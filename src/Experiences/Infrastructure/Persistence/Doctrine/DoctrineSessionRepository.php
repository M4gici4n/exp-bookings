<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Exception\SessionNotFoundException;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
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

    public function getForModification(SessionId $id): Session
    {
        return $this->entityManager->find(Session::class, $id, LockMode::PESSIMISTIC_WRITE)
            ?? throw SessionNotFoundException::withId($id);
    }

    public function allByExperience(ExperienceId $experienceId): array
    {
        return $this->entityManager
            ->createQuery(
                'SELECT s FROM ' . Session::class . ' s WHERE s.experienceId = :experienceId ORDER BY s.startsAt ASC'
            )
            ->setParameter('experienceId', $experienceId)
            ->getResult();
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
}
