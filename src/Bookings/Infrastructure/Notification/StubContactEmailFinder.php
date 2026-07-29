<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Notification;

use App\Bookings\Application\Notification\ContactEmailFinderInterface;
use App\Bookings\Domain\ValueObject\UserId;

final class StubContactEmailFinder implements ContactEmailFinderInterface
{
    public function findByUser(UserId $userId): string
    {
        return sprintf('user-%s@ex-bookings.com', strtolower($userId->value()));
    }
}
