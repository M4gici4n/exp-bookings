<?php declare(strict_types=1);

namespace App\Bookings\Application\Query\GetBooking;

use App\Bookings\Domain\Booking;

final readonly class BookingView
{
    public function __construct(
        public string $id,
        public string $sessionId,
        public string $userId,
        public int $spots,
        public string $status,
        public int $totalPriceAmount,
        public string $totalPriceCurrency,
    ) {}

    public static function fromEntity(Booking $booking): self
    {
        return new self(
            $booking->id()->value(),
            $booking->sessionId()->value(),
            $booking->userId()->value(),
            $booking->spots(),
            $booking->status()->value,
            $booking->totalPrice()->amount(),
            $booking->totalPrice()->currency()->value,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sessionId' => $this->sessionId,
            'userId' => $this->userId,
            'spots' => $this->spots,
            'status' => $this->status,
            'totalPrice' => [
                'amount' => $this->totalPriceAmount,
                'currency' => $this->totalPriceCurrency,
            ],
        ];
    }
}
