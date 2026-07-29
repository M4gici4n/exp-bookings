<?php declare(strict_types=1);

namespace App\Experiences\Application\UseCase\RegisterExperience;

use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceCommand;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use App\Shared\Application\Bus\Command\CommandHandlerInterface;
use App\Shared\Domain\Event\EventDispatcherInterface;

final class RegisterExperienceHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
        private readonly EventDispatcherInterface $eventDispatcher,
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

        foreach ($experience->pullEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
