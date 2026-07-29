<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Notification;

use App\Bookings\Application\Notification\EmailMessage;
use App\Bookings\Application\Notification\MailerInterface;

final class RecordingMailer implements MailerInterface
{
    /** @var EmailMessage[] */
    public array $sent = [];

    public function send(EmailMessage $message): void
    {
        $this->sent[] = $message;
    }
}
