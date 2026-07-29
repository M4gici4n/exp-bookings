<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\GetExperience;

final readonly class GetExperienceQuery
{
    public function __construct(
        public string $experienceId,
    ) {}
}
