<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\Event\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyDispatcherInterface;

final class SymfonyEventDispatcher implements EventDispatcherInterface
{
    public function __construct(private readonly SymfonyDispatcherInterface $dispatcher) {}

    public function dispatch(DomainEventInterface $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
