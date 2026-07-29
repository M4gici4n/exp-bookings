<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\BookSeats;

use App\Bookings\Application\UseCase\BookSeats\BookSeatsCommand;
use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\UserId;
use App\Experiences\Domain\Exception\SessionAlreadyStartedException;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Application\Bus\Command\CommandHandlerInterface;
use App\Shared\Domain\Event\EventDispatcherInterface;
use App\Shared\Domain\Service\ClockInterface;

final class BookSeatsHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessions,
        private readonly BookingRepositoryInterface $bookings,
        private readonly ClockInterface $clock,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(BookSeatsCommand $command): void
    {
        $session = $this->sessions->get(SessionId::of($command->sessionId));

        if ($session->hasStarted($this->clock->now())) {
            throw SessionAlreadyStartedException::withId($session->id());
        }

        $totalPrice = $session->priceFor($command->seats);

        $this->sessions->reserveSeats($session->id(), $command->seats);

        $booking = Booking::confirm(
            BookingId::of($command->bookingId),
            $session->id(),
            UserId::of($command->userId),
            $command->seats,
            $totalPrice,
        );

        $this->bookings->save($booking);

        foreach ($booking->pullEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
