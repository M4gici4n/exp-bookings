<?php declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Event;

use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\Event\EventDispatcherInterface;

final class InMemoryEventDispatcher implements EventDispatcherInterface
{
    /** @var DomainEventInterface[] */
    private array $events = [];

    public function dispatch(DomainEventInterface $event): void
    {
        $this->events[] = $event;
    }

    /** @return DomainEventInterface[] */
    public function dispatchedEvents(): array
    {
        return $this->events;
    }
}
