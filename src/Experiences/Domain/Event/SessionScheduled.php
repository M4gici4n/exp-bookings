<?php declare(strict_types=1);

namespace App\Experiences\Domain\Event;

use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Event\AbstractDomainEvent;
use DateTimeImmutable;

final class SessionScheduled extends AbstractDomainEvent
{
    public function __construct(
        private readonly SessionId $sessionId,
        private readonly ExperienceId $experienceId,
        private readonly DateTimeImmutable $startsAt,
    ) {
        parent::__construct();
    }

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function experienceId(): ExperienceId
    {
        return $this->experienceId;
    }

    public function startsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }
}
