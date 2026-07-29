<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Shared\Domain\Exception\DomainException;

final class ExperienceNotFoundException extends DomainException
{
    public static function withId(ExperienceId $id): self
    {
        return new self("Experience {$id} not found.");
    }
}
