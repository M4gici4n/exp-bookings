<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\Query\GetExperience\ExperienceView;
use App\Experiences\Application\Query\ListExperiences\ListExperiencesQuery;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListExperiencesController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/experiences', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var ExperienceView[] $views */
        $views = $this->queryBus->query(new ListExperiencesQuery());

        $items = [];

        foreach ($views as $view) {
            $items[] = $view->toArray();
        }

        return $this->responseBuilder->data($items);
    }
}
