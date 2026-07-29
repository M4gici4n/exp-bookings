<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Persistence\Doctrine\Type;

use App\Bookings\Domain\ValueObject\UserId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractUlidType;

final class UserIdType extends AbstractUlidType
{
    public const NAME = 'user_id';

    protected function targetClass(): string
    {
        return UserId::class;
    }
}
