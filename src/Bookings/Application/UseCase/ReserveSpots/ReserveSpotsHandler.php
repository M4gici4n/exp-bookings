<?php declare(strict_types=1);

namespace App\Bookings\Application\UseCase\ReserveSpots;

use App\Bookings\Application\UseCase\ReserveSpots\ReserveSpotsCommand;
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

final class ReserveSpotsHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessions,
        private readonly BookingRepositoryInterface $bookings,
        private readonly ClockInterface $clock,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ReserveSpotsCommand $command): void
    {
        $session = $this->sessions->getForModification(SessionId::of($command->sessionId));

        if ($session->hasStarted($this->clock->now())) {
            throw SessionAlreadyStartedException::withId($session->id());
        }

        $totalPrice = $session->totalPriceFor($command->spots);

        $session->reserve($command->spots);

        $booking = Booking::create(
            BookingId::of($command->bookingId),
            $session->id(),
            UserId::of($command->userId),
            $command->spots,
            $totalPrice,
        );

        $this->bookings->save($booking);

        foreach ($booking->pullEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
