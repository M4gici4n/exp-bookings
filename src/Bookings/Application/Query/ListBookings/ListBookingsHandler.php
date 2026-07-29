<?php declare(strict_types=1);

namespace App\Bookings\Application\Query\ListBookings;

use App\Bookings\Application\Query\GetBooking\BookingView;
use App\Bookings\Application\Query\ListBookings\ListBookingsQuery;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class ListBookingsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessions,
        private readonly BookingRepositoryInterface $bookings,
    ) {}

    /** @return BookingView[] */
    public function __invoke(ListBookingsQuery $query): array
    {
        $sessionId = SessionId::of($query->sessionId);

        $this->sessions->get($sessionId);

        $views = [];

        foreach ($this->bookings->allBySession($sessionId) as $booking) {
            $views[] = BookingView::fromEntity($booking);
        }

        return $views;
    }
}
