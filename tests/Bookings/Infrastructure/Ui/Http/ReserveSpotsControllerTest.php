<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ReserveSpotsControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FBW';
    private const string UNKNOWN_SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testBooksSpotsAndReturnsCreatedWithLocation(): void
    {
        $sessionId = $this->createSession(20);

        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID, 'spots' => 3]);

        self::assertSame(Response::HTTP_CREATED, $this->statusCode());

        $id = $this->json()['data']['id'];
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id);
        self::assertSame('/bookings/' . $id, $this->client->getResponse()->headers->get('Location'));
    }

    public function testBookingMoreSpotsThanAvailableReturnsConflict(): void
    {
        $sessionId = $this->createSession(2);

        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID, 'spots' => 3]);

        self::assertSame(Response::HTTP_CONFLICT, $this->statusCode());
        self::assertSame('NOT_ENOUGH_SPOTS', $this->json()['error']['code']);
    }

    public function testUnknownSessionReturnsNotFound(): void
    {
        $this->postJson('/sessions/' . self::UNKNOWN_SESSION_ID . '/bookings', ['userId' => self::USER_ID, 'spots' => 1]);

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('SESSION_NOT_FOUND', $this->json()['error']['code']);
    }

    public function testMissingSpotsReturnsValidationError(): void
    {
        $sessionId = $this->createSession(20);

        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());

        $error = $this->json()['error'];
        self::assertSame('VALIDATION_FAILED', $error['code']);
        self::assertSame('spots', $error['details']['errors'][0]['target']);
    }

    private function createSession(int $maxCapacity): string
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A walk through the old town.',
        ]);
        $experienceId = $this->json()['data']['id'];

        $this->postJson("/experiences/{$experienceId}/sessions", [
            'startsAt' => (new DateTimeImmutable('+10 days'))->format(DateTimeImmutable::ATOM),
            'maxCapacity' => $maxCapacity,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ]);

        return $this->json()['data']['id'];
    }
}
