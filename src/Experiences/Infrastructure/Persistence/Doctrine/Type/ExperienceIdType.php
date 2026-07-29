<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine\Type;

use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractUlidType;

final class ExperienceIdType extends AbstractUlidType
{
    public const NAME = 'experience_id';

    protected function targetClass(): string
    {
        return ExperienceId::class;
    }
}
