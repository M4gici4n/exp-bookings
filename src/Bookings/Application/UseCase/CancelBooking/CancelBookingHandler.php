<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\CancelBooking;

use App\Bookings\Application\UseCase\CancelBooking\CancelBookingCommand;
use App\Bookings\Domain\Exception\LateCancellationException;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Shared\Domain\Service\ClockInterface;
use DateInterval;

final class CancelBookingHandler
{
    private const string CANCELLATION_WINDOW = 'PT24H';

    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
        private readonly SessionRepositoryInterface $sessions,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(CancelBookingCommand $command): void
    {
        $booking = $this->bookings->get(BookingId::of($command->bookingId));
        $session = $this->sessions->get($booking->sessionId());

        $deadline = $session->startsAt()->sub(new DateInterval(self::CANCELLATION_WINDOW));

        if ($this->clock->now() >= $deadline) {
            throw LateCancellationException::within24HoursOf($session->startsAt());
        }

        $booking->cancel();

        $this->bookings->save($booking);
        $this->sessions->releaseSeats($booking->sessionId(), $booking->seats());
    }
}
