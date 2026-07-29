<?php declare(strict_types=1);

namespace App\Bookings\Domain\Event;

use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\UserId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Event\AbstractDomainEvent;
use App\Shared\Domain\ValueObject\Money;

final class BookingConfirmed extends AbstractDomainEvent
{
    public function __construct(
        private readonly BookingId $bookingId,
        private readonly SessionId $sessionId,
        private readonly UserId $userId,
        private readonly int $seats,
        private readonly Money $totalPrice,
    ) {
        parent::__construct();
    }

    public function bookingId(): BookingId
    {
        return $this->bookingId;
    }

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function seats(): int
    {
        return $this->seats;
    }

    public function totalPrice(): Money
    {
        return $this->totalPrice;
    }
}
