<?php declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Event\DomainEventInterface;

trait RecordsEvents
{
    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    protected function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /** @return DomainEventInterface[] */
    public function pullEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
