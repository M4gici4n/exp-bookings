<?php declare(strict_types=1);

namespace App\Tests\Bookings\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ListBookingsControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string USER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FBW';
    private const string UNKNOWN_SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testListsBookingsOfTheSession(): void
    {
        $sessionId = $this->createSession();
        $first = $this->book($sessionId, 2);
        $second = $this->book($sessionId, 3);

        $this->get("/sessions/{$sessionId}/bookings");

        self::assertSame(Response::HTTP_OK, $this->statusCode());

        $data = $this->json()['data'];
        self::assertCount(2, $data);

        $ids = array_column($data, 'id');
        self::assertContains($first, $ids);
        self::assertContains($second, $ids);
    }

    public function testUnknownSessionReturnsNotFound(): void
    {
        $this->get('/sessions/' . self::UNKNOWN_SESSION_ID . '/bookings');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('SESSION_NOT_FOUND', $this->json()['error']['code']);
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

    private function book(string $sessionId, int $spots): string
    {
        $this->postJson("/sessions/{$sessionId}/bookings", ['userId' => self::USER_ID, 'spots' => $spots]);

        return $this->json()['data']['id'];
    }
}
