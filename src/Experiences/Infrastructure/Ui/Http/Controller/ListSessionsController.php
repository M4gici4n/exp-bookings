<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\Query\GetSession\SessionView;
use App\Experiences\Application\Query\ListSessions\ListSessionsQuery;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListSessionsController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/experiences/{experienceId}/sessions', methods: ['GET'])]
    public function __invoke(string $experienceId): JsonResponse
    {
        /** @var SessionView[] $views */
        $views = $this->queryBus->query(new ListSessionsQuery($experienceId));

        $items = [];

        foreach ($views as $view) {
            $items[] = $view->toArray();
        }

        return $this->responseBuilder->data($items);
    }
}
