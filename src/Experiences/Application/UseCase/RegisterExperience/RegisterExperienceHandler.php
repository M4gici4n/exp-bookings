<?php declare(strict_types=1);

namespace App\Experiences\Application\UseCase\RegisterExperience;

use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceCommand;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;

final class RegisterExperienceHandler
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
    ) {}

    public function __invoke(RegisterExperienceCommand $command): void
    {
        $experience = Experience::register(
            ExperienceId::of($command->experienceId),
            ProviderId::of($command->providerId),
            $command->title,
            $command->description,
        );

        $this->experiences->save($experience);
    }
}
