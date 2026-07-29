<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetBookingControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FBW';
    private const string UNKNOWN_BOOKING_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testReturnsTheBooking(): void
    {
        $sessionId = $this->createSession();

        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID, 'seats' => 2]);
        $id = $this->json()['data']['id'];

        $this->get("/bookings/{$id}");

        self::assertSame(Response::HTTP_OK, $this->statusCode());
        self::assertSame([
            'id' => $id,
            'sessionId' => $sessionId,
            'userId' => self::USER_ID,
            'seats' => 2,
            'status' => 'confirmed',
            'totalPrice' => ['amount' => 9000, 'currency' => 'EUR'],
        ], $this->json()['data']);
    }

    public function testUnknownBookingReturnsNotFound(): void
    {
        $this->get('/bookings/' . self::UNKNOWN_BOOKING_ID);

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('BOOKING_NOT_FOUND', $this->json()['error']['code']);
    }

    private function createSession(): string
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A walk through the old town.',
        ]);
        $experienceId = $this->json()['data']['id'];

        $this->postJson("/experiences/{$experienceId}/sessions", [
            'startsAt' => (new DateTimeImmutable('+10 days'))->format(DateTimeImmutable::ATOM),
            'maxCapacity' => 20,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ]);

        return $this->json()['data']['id'];
    }
}
