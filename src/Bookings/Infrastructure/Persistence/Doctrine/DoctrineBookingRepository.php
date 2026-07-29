<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Persistence\Doctrine;

use App\Bookings\Domain\Booking;
use App\Bookings\Domain\Exception\BookingNotFoundException;
use App\Bookings\Domain\Repository\BookingRepositoryInterface;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Experiences\Domain\ValueObject\SessionId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineBookingRepository implements BookingRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(Booking $booking): void
    {
        $this->entityManager->persist($booking);
    }

    public function get(BookingId $id): Booking
    {
        return $this->entityManager->find(Booking::class, $id)
            ?? throw BookingNotFoundException::withId($id);
    }

    public function allBySession(SessionId $sessionId): array
    {
        return $this->entityManager
            ->createQuery('SELECT b FROM ' . Booking::class . ' b WHERE b.sessionId = :sessionId ORDER BY b.id ASC')
            ->setParameter('sessionId', $sessionId)
            ->getResult();
    }
}
