<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Ui\Http\Controller;

use App\Experiences\Application\UseCase\CreateSession\CreateSessionCommand;
use App\Shared\Application\Bus\Command\CommandBusInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Shared\Infrastructure\Ui\Http\JsonRequest;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateSessionController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly UlidGeneratorInterface $ulidGenerator,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/experiences/{experienceId}/sessions', methods: ['POST'])]
    public function __invoke(string $experienceId, Request $request): JsonResponse
    {
        $json = new JsonRequest($request);
        $startsAt = $json->requiredDateTimeString('startsAt');
        $maxCapacity = $json->requiredInt('maxCapacity');
        $priceAmount = $json->requiredInt('priceAmount');
        $priceCurrency = $json->requiredString('priceCurrency');
        $json->validate();

        $sessionId = $this->ulidGenerator->generate()->value();

        $this->commandBus->dispatch(new CreateSessionCommand(
            $sessionId,
            $experienceId,
            $startsAt,
            $maxCapacity,
            $priceAmount,
            $priceCurrency,
        ));

        return $this->responseBuilder->created(
            ['id' => $sessionId],
            '/sessions/' . $sessionId,
        );
    }
}
