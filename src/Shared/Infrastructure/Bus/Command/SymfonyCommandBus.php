<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\Command;

use App\Shared\Application\Bus\Command\CommandBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyCommandBus implements CommandBusInterface
{
    public function __construct(
        #[Autowire(service: 'command.bus')] private readonly MessageBusInterface $messageBus,
    ) {}

    public function dispatch(object $command): void
    {
        try {
            $this->messageBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            throw current($e->getWrappedExceptions()) ?: $e;
        }
    }
}
