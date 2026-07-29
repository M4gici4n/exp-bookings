<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\Query\GetSession\GetSessionQuery;
use App\Experiences\Application\Query\GetSession\SessionView;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetSessionController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/sessions/{sessionId}', methods: ['GET'])]
    public function __invoke(string $sessionId): JsonResponse
    {
        /** @var SessionView $view */
        $view = $this->queryBus->query(new GetSessionQuery($sessionId));

        return $this->responseBuilder->data($view->toArray());
    }
}
