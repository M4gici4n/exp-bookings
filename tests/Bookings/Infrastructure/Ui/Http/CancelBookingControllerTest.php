<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class CancelBookingControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FBW';
    private const string UNKNOWN_BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testCancelsConfirmedBookingAndReturnsStatus(): void
    {
        $bookingId = $this->createBooking('+10 days');

        $this->post("/bookings/{$bookingId}/cancellation");

        self::assertSame(Response::HTTP_OK, $this->statusCode());
        self::assertSame(['id' => $bookingId, 'status' => 'cancelled'], $this->json()['data']);
    }

    public function testCancellingAnAlreadyCancelledBookingReturnsConflict(): void
    {
        $bookingId = $this->createBooking('+10 days');

        $this->post("/bookings/{$bookingId}/cancellation");
        self::assertSame(Response::HTTP_OK, $this->statusCode());

        $this->post("/bookings/{$bookingId}/cancellation");

        self::assertSame(Response::HTTP_CONFLICT, $this->statusCode());
        self::assertSame('BOOKING_ALREADY_CANCELLED', $this->json()['error']['code']);
    }

    public function testUnknownBookingReturnsNotFound(): void
    {
        $this->post('/bookings/' . self::UNKNOWN_BOOKING_ID . '/cancellation');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('BOOKING_NOT_FOUND', $this->json()['error']['code']);
    }

    public function testCancellingWithinTheDeadlineReturnsUnprocessable(): void
    {
        $bookingId = $this->createBooking('+12 hours');

        $this->post("/bookings/{$bookingId}/cancellation");

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());
        self::assertSame('LATE_CANCELLATION', $this->json()['error']['code']);
    }

    private function createBooking(string $startsAt): string
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A walk through the old town.',
        ]);
        $experienceId = $this->json()['data']['id'];

        $this->postJson("/experiences/{$experienceId}/sessions", [
            'startsAt' => (new DateTimeImmutable($startsAt))->format(DateTimeImmutable::ATOM),
            'maxCapacity' => 20,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ]);
        $sessionId = $this->json()['data']['id'];

        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID, 'seats' => 2]);

        return $this->json()['data']['id'];
    }
}
