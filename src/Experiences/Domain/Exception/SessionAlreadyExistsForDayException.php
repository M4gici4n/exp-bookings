<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;
use DateTimeImmutable;

final class SessionAlreadyExistsForDayException extends DomainException
{
    public static function forExperienceOnDay(ExperienceId $experienceId, DateTimeImmutable $day): self
    {
        return new self("Experience {$experienceId} already has a session on {$day->format('Y-m-d')}.");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::Conflict;
    }
}
