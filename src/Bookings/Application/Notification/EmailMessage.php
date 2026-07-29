<?php declare(strict_types=1);

namespace App\Bookings\Application\Notification;

final readonly class EmailMessage
{
    public function __construct(
        public string $to,
        public string $subject,
        public string $body,
    ) {}
}
