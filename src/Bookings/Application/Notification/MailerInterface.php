<?php declare(strict_types=1);

namespace App\Bookings\Application\Notification;

interface MailerInterface
{
    public function send(EmailMessage $message): void;
}
