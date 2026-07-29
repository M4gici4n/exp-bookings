<?php declare(strict_types=1);

namespace App\Experiences\Application\UseCase\RegisterExperience;

final readonly class RegisterExperienceCommand
{
    public function __construct(
        public string $experienceId,
        public string $providerId,
        public string $title,
        public string $description,
    ) {
    }
}
