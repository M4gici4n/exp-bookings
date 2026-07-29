<?php declare(strict_types=1);

namespace App\Experiences\Domain\Event;

use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use App\Shared\Domain\Event\AbstractDomainEvent;

final class ExperienceRegistered extends AbstractDomainEvent
{
    public function __construct(
        private readonly ExperienceId $experienceId,
        private readonly ProviderId $providerId,
    ) {
        parent::__construct();
    }

    public function experienceId(): ExperienceId
    {
        return $this->experienceId;
    }

    public function providerId(): ProviderId
    {
        return $this->providerId;
    }
}
