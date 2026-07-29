<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\Query\GetExperience\ExperienceView;
use App\Experiences\Application\Query\GetExperience\GetExperienceQuery;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetExperienceController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/experiences/{experienceId}', methods: ['GET'])]
    public function __invoke(string $experienceId): JsonResponse
    {
        /** @var ExperienceView $view */
        $view = $this->queryBus->query(new GetExperienceQuery($experienceId));

        return $this->responseBuilder->data($view->toArray());
    }
}
