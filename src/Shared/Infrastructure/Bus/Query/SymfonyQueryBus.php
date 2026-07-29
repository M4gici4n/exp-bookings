<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus\Query;

use App\Shared\Application\Bus\Query\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyQueryBus implements QueryBusInterface
{
    use HandleTrait;

    public function __construct(
        #[Autowire(service: 'query.bus')] MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function query(object $query): mixed
    {
        try {
            return $this->handle($query);
        } catch (HandlerFailedException $e) {
            throw current($e->getWrappedExceptions()) ?: $e;
        }
    }
}
