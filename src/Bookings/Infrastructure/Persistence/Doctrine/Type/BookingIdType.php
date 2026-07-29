<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Persistence\Doctrine\Type;

use App\Bookings\Domain\ValueObject\BookingId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractUlidType;

final class BookingIdType extends AbstractUlidType
{
    public const NAME = 'booking_id';

    protected function targetClass(): string
    {
        return BookingId::class;
    }
}
