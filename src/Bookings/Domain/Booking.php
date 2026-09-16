<?php declare(strict_types=1);

namespace App\Bookings\Domain;

use App\Bookings\Domain\Event\BookingCancelled;
use App\Bookings\Domain\Event\BookingConfirmed;
use App\Bookings\Domain\Exception\BookingAlreadyCancelledException;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\BookingStatus;
use App\Bookings\Domain\ValueObject\UserId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Entity\AggregateRoot;
use App\Shared\Domain\ValueObject\Money;

final class Booking extends AggregateRoot
{
    private BookingId $id;
    private SessionId $sessionId;
    private UserId $userId;
    private int $spots;
    private Money $totalPrice;
    private BookingStatus $status;

    private function __construct(
        BookingId $id,
        SessionId $sessionId,
        UserId $userId,
        int $spots,
        Money $totalPrice,
        BookingStatus $status,
    ) {
        parent::__construct();

        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->spots = $spots;
        $this->totalPrice = $totalPrice;
        $this->status = $status;
    }

    public static function create(
        BookingId $id,
        SessionId $sessionId,
        UserId $userId,
        int $spots,
        Money $totalPrice,
    ): self {
        $booking = new self($id, $sessionId, $userId, $spots, $totalPrice, BookingStatus::CONFIRMED);
        $booking->recordEvent(new BookingConfirmed($id, $sessionId, $userId, $spots, $totalPrice));

        return $booking;
    }

    public function cancel(): void
    {
        if ($this->status->isCancelled()) {
            throw BookingAlreadyCancelledException::withId($this->id);
        }

        $this->status = BookingStatus::CANCELLED;
        $this->recordEvent(new BookingCancelled($this->id, $this->sessionId, $this->userId, $this->spots));
    }

    public function id(): BookingId
    {
        return $this->id;
    }

    public function sessionId(): SessionId
    {
        return $this->sessionId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function spots(): int
    {
        return $this->spots;
    }

    public function totalPrice(): Money
    {
        return $this->totalPrice;
    }

    public function status(): BookingStatus
    {
        return $this->status;
    }
}
