<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceCommand;
use App\Shared\Application\Bus\Command\CommandBusInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Shared\Infrastructure\Ui\Http\JsonRequest;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterExperienceController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly UlidGeneratorInterface $ulidGenerator,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/experiences', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $json = new JsonRequest($request);
        $providerId = $json->requiredString('providerId');
        $title = $json->requiredString('title');
        $description = $json->requiredString('description');
        $json->validate();

        $experienceId = $this->ulidGenerator->generate()->value();

        $this->commandBus->dispatch(new RegisterExperienceCommand(
            $experienceId,
            $providerId,
            $title,
            $description,
        ));

        return $this->responseBuilder->created(
            ['id' => $experienceId],
            '/experiences/' . $experienceId,
        );
    }
}
