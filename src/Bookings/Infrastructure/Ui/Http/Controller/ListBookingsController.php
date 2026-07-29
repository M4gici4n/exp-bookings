<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Ui\Http\Controller;

use App\Bookings\Application\Query\GetBooking\BookingView;
use App\Bookings\Application\Query\ListBookings\ListBookingsQuery;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListBookingsController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/sessions/{sessionId}/bookings', methods: ['GET'])]
    public function __invoke(string $sessionId): JsonResponse
    {
        /** @var BookingView[] $views */
        $views = $this->queryBus->query(new ListBookingsQuery($sessionId));

        $items = [];

        foreach ($views as $view) {
            $items[] = $view->toArray();
        }

        return $this->responseBuilder->data($items);
    }
}
