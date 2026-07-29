<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\GetSession;

use App\Experiences\Application\Query\GetSession\GetSessionQuery;
use App\Experiences\Application\Query\GetSession\SessionView;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class GetSessionHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessions,
    ) {}

    public function __invoke(GetSessionQuery $query): SessionView
    {
        $session = $this->sessions->get(SessionId::of($query->sessionId));

        return SessionView::fromEntity($session);
    }
}
