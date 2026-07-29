<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Notification;

use App\Bookings\Application\Notification\ContactEmailFinderInterface;
use App\Bookings\Domain\Event\BookingCancelled;
use App\Bookings\Domain\Event\BookingConfirmed;
use App\Bookings\Domain\ValueObject\BookingId;
use App\Bookings\Domain\ValueObject\UserId;
use App\Bookings\Infrastructure\Notification\BookingEmailNotifier;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\ValueObject\Currency;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class BookingEmailNotifierTest extends TestCase
{
    private const BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const CONTACT_EMAIL = 'contact@example.test';
    private const SEATS = 3;

    private RecordingMailer $mailer;
    private BookingEmailNotifier $notifier;

    protected function setUp(): void
    {
        $this->mailer = new RecordingMailer();
        $this->notifier = new BookingEmailNotifier($this->mailer, $this->contactFinder());
    }

    public function testItSendsAnEmailWhenABookingIsConfirmed(): void
    {
        $this->notifier->onBookingConfirmed(new BookingConfirmed(
            BookingId::of(self::BOOKING_ID),
            SessionId::of(self::SESSION_ID),
            UserId::of(self::USER_ID),
            self::SEATS,
            Money::of(7500, Currency::EUR),
        ));

        self::assertCount(1, $this->mailer->sent);
        self::assertSame(self::CONTACT_EMAIL, $this->mailer->sent[0]->to);
        self::assertStringContainsString('confirmed', $this->mailer->sent[0]->subject);
    }

    public function testItSendsAnEmailWhenABookingIsCancelled(): void
    {
        $this->notifier->onBookingCancelled(new BookingCancelled(
            BookingId::of(self::BOOKING_ID),
            SessionId::of(self::SESSION_ID),
            UserId::of(self::USER_ID),
            self::SEATS,
        ));

        self::assertCount(1, $this->mailer->sent);
        self::assertSame(self::CONTACT_EMAIL, $this->mailer->sent[0]->to);
        self::assertStringContainsString('cancelled', $this->mailer->sent[0]->subject);
    }

    private function contactFinder(): ContactEmailFinderInterface
    {
        return new class(self::CONTACT_EMAIL) implements ContactEmailFinderInterface {
            public function __construct(private readonly string $email) {}

            public function findByUser(UserId $userId): string
            {
                return $this->email;
            }
        };
    }
}
