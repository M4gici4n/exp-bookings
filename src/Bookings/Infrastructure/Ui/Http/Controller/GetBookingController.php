<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Ui\Http\Controller;

use App\Bookings\Application\Query\GetBooking\BookingView;
use App\Bookings\Application\Query\GetBooking\GetBookingQuery;
use App\Shared\Application\Bus\Query\QueryBusInterface;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetBookingController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly ResponseBuilder $responseBuilder,
    ) {}

    #[Route('/bookings/{bookingId}', methods: ['GET'])]
    public function __invoke(string $bookingId): JsonResponse
    {
        /** @var BookingView $view */
        $view = $this->queryBus->query(new GetBookingQuery($bookingId));

        return $this->responseBuilder->data($view->toArray());
    }
}
