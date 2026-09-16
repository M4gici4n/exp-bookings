<?php declare(strict_types=1);

namespace App\Bookings\Infrastructure\Notification;

use App\Bookings\Application\Notification\ContactEmailFinderInterface;
use App\Bookings\Application\Notification\EmailMessage;
use App\Bookings\Application\Notification\MailerInterface;
use App\Bookings\Domain\Event\BookingCancelled;
use App\Bookings\Domain\Event\BookingConfirmed;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class BookingEmailNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ContactEmailFinderInterface $contactEmails,
    ) {}

    #[AsEventListener]
    public function onBookingConfirmed(BookingConfirmed $event): void
    {
        $this->mailer->send(new EmailMessage(
            $this->contactEmails->findByUser($event->userId()),
            'Your booking is confirmed',
            sprintf('Your booking %s for %d spot(s) has been confirmed.', $event->bookingId(), $event->spots()),
        ));
    }

    #[AsEventListener]
    public function onBookingCancelled(BookingCancelled $event): void
    {
        $this->mailer->send(new EmailMessage(
            $this->contactEmails->findByUser($event->userId()),
            'Your booking has been cancelled',
            sprintf('Your booking %s for %d spot(s) has been cancelled.', $event->bookingId(), $event->spots()),
        ));
    }
}
