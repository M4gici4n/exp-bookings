<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class ListSessionsControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string UNKNOWN_EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testListsSessionsOfTheExperience(): void
    {
        $experienceId = $this->createExperience();
        $first = $this->createSession($experienceId, '+10 days');
        $second = $this->createSession($experienceId, '+11 days');

        $this->get("/experiences/{$experienceId}/sessions");

        self::assertSame(Response::HTTP_OK, $this->statusCode());

        $data = $this->json()['data'];
        self::assertCount(2, $data);
        self::assertSame([$first, $second], array_column($data, 'id'));
    }

    public function testUnknownExperienceReturnsNotFound(): void
    {
        $this->get('/experiences/' . self::UNKNOWN_EXPERIENCE_ID . '/sessions');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('EXPERIENCE_NOT_FOUND', $this->json()['error']['code']);
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

    private function createSession(string $experienceId, string $startsAt): string
    {
        $this->postJson("/experiences/{$experienceId}/sessions", [
            'startsAt' => (new DateTimeImmutable($startsAt))->format(DateTimeImmutable::ATOM),
            'maxCapacity' => 20,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ]);

        return $this->json()['data']['id'];
    }
}
