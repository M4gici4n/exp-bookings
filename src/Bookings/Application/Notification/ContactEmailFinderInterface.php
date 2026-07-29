<?php declare(strict_types=1);

namespace App\Bookings\Application\Notification;

use App\Bookings\Domain\ValueObject\UserId;

interface ContactEmailFinderInterface
{
    public function findByUser(UserId $userId): string;
}
