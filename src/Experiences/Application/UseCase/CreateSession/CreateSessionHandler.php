<?php declare(strict_types=1);

namespace App\Experiences\Application\UseCase\CreateSession;

use App\Experiences\Application\UseCase\CreateSession\CreateSessionCommand;
use App\Experiences\Domain\Exception\SessionAlreadyExistsForDayException;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\Session;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Service\ClockInterface;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;

final class CreateSessionHandler
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
        private readonly SessionRepositoryInterface $sessions,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(CreateSessionCommand $command): void
    {
        $experienceId = ExperienceId::of($command->experienceId);

        $this->experiences->get($experienceId);

        $startsAt = new DateTimeImmutable($command->startsAt);

        if ($this->sessions->existsForExperienceOnDay($experienceId, $startsAt)) {
            throw SessionAlreadyExistsForDayException::forExperienceOnDay($experienceId, $startsAt);
        }

        $session = Session::schedule(
            SessionId::of($command->sessionId),
            $experienceId,
            $startsAt,
            $command->maxCapacity,
            Money::of($command->priceAmount, Currency::fromCode($command->priceCurrency)),
            $this->clock->now(),
        );

        $this->sessions->save($session);
    }
}
