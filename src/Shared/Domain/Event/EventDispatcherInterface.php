<?php declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Shared\Domain\Event\DomainEventInterface;

interface EventDispatcherInterface
{
    public function dispatch(DomainEventInterface $event): void;
}
