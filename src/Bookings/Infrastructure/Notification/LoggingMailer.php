<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Notification;

use App\Bookings\Application\Notification\EmailMessage;
use App\Bookings\Application\Notification\MailerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('notification')]
final class LoggingMailer implements MailerInterface
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(EmailMessage $message): void
    {
        $this->logger->info('Booking notification email dispatched', [
            'subject' => $message->subject,
        ]);
    }
}
