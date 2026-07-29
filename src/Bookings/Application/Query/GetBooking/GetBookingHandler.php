<?php declare(strict_types=1);

namespace App\Bookings\Application\Query\GetBooking;

use App\Bookings\Application\Query\GetBooking\BookingView;
use App\Bookings\Application\Query\GetBooking\GetBookingQuery;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class GetBookingHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
    ) {}

    public function __invoke(GetBookingQuery $query): BookingView
    {
        $booking = $this->bookings->get(BookingId::of($query->bookingId));

        return BookingView::fromEntity($booking);
    }
}
