<?php declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Event\DomainEventInterface;
use DateTimeImmutable;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private readonly DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
