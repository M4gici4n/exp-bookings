<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class GetSessionControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string UNKNOWN_SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testReturnsTheSession(): void
    {
        $experienceId = $this->createExperience();
        $startsAt = (new DateTimeImmutable('+10 days'))->format(DateTimeImmutable::ATOM);

        $this->postJson("/experiences/{$experienceId}/sessions", [
            'startsAt' => $startsAt,
            'maxCapacity' => 20,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ]);
        $id = $this->json()['data']['id'];

        $this->get("/sessions/{$id}");

        self::assertSame(Response::HTTP_OK, $this->statusCode());
        self::assertSame([
            'id' => $id,
            'experienceId' => $experienceId,
            'startsAt' => $startsAt,
            'maxCapacity' => 20,
            'availableSpots' => 20,
            'price' => ['amount' => 4500, 'currency' => 'EUR'],
        ], $this->json()['data']);
    }

    public function testUnknownSessionReturnsNotFound(): void
    {
        $this->get('/sessions/' . self::UNKNOWN_SESSION_ID);

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('SESSION_NOT_FOUND', $this->json()['error']['code']);
    }

    private function createExperience(): string
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A walk through the old town.',
        ]);

        return $this->json()['data']['id'];
    }
}
