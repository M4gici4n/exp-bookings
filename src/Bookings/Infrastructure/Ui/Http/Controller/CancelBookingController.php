<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Ui\Http\Controller;

use App\Bookings\Application\UseCase\CancelBooking\CancelBookingCommand;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Shared\Application\Bus\Command\CommandBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class CancelBookingController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/bookings/{bookingId}/cancellation', methods: ['POST'])]
    public function __invoke(string $bookingId): JsonResponse
    {
        $this->commandBus->dispatch(new CancelBookingCommand($bookingId));

        return $this->responseBuilder->data([
            'id' => $bookingId,
            'status' => BookingStatus::CANCELLED->value,
        ]);
    }
}
