<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Ui\Http\Controller;

use App\Bookings\Application\UseCase\ReserveSpots\ReserveSpotsCommand;
use App\Shared\Application\Bus\Command\CommandBusInterface;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Shared\Infrastructure\Ui\Http\JsonRequest;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ReserveSpotsController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly UlidGeneratorInterface $ulidGenerator,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/sessions/{sessionId}/bookings', methods: ['POST'])]
    public function __invoke(string $sessionId, Request $request): JsonResponse
    {
        $json = new JsonRequest($request);
        $userId = $json->requiredString('userId');
        $spots = $json->requiredInt('spots');
        $json->validate();

        $bookingId = $this->ulidGenerator->generate()->value();

        $this->commandBus->dispatch(new ReserveSpotsCommand(
            $bookingId,
            $sessionId,
            $userId,
            $spots,
        ));

        return $this->responseBuilder->created(
            ['id' => $bookingId],
            '/bookings/' . $bookingId,
        );
    }
}
