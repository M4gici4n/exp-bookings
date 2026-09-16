<?php declare(strict_types=1);

namespace App\Experiences\Domain\Repository;

use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use DateTimeImmutable;

interface SessionRepositoryInterface
{
    public function save(Session $session): void;

    public function get(SessionId $id): Session;

    public function getForModification(SessionId $id): Session;

    /** @return Session[] */
    public function allByExperience(ExperienceId $experienceId): array;

    public function existsForExperienceOnDay(ExperienceId $experienceId, DateTimeImmutable $day): bool;
}
