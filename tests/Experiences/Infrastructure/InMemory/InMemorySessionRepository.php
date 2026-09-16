<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\InMemory;

use App\Experiences\Domain\Exception\SessionNotFoundException;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use DateTimeImmutable;

final class InMemorySessionRepository implements SessionRepositoryInterface
{
    /** @var array<string, Session> */
    private array $sessions = [];

    public function save(Session $session): void
    {
        $this->sessions[$session->id()->value()] = $session;
    }

    public function get(SessionId $id): Session
    {
        return $this->sessions[$id->value()] ?? throw SessionNotFoundException::withId($id);
    }

    public function getForModification(SessionId $id): Session
    {
        return $this->get($id);
    }

    public function allByExperience(ExperienceId $experienceId): array
    {
        $matches = [];

        foreach ($this->sessions as $session) {
            if ($session->experienceId()->equals($experienceId)) {
                $matches[] = $session;
            }
        }

        return $matches;
    }

    public function existsForExperienceOnDay(ExperienceId $experienceId, DateTimeImmutable $day): bool
    {
        foreach ($this->sessions as $session) {
            if ($session->experienceId()->equals($experienceId)
                && $session->startsAt()->format('Y-m-d') === $day->format('Y-m-d')
            ) {
                return true;
            }
        }

        return false;
    }
}
