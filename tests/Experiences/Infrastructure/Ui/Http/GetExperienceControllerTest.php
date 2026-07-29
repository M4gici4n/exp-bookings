<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

final class GetExperienceControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string UNKNOWN_EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testReturnsTheExperience(): void
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A two-hour walk through the old town.',
        ]);
        $id = $this->json()['data']['id'];

        $this->get("/experiences/{$id}");

        self::assertSame(Response::HTTP_OK, $this->statusCode());
        self::assertSame([
            'id' => $id,
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A two-hour walk through the old town.',
        ], $this->json()['data']);
    }

    public function testUnknownExperienceReturnsNotFound(): void
    {
        $this->get('/experiences/' . self::UNKNOWN_EXPERIENCE_ID);

        self::assertSame(Response::HTTP_NOT_FOUND, $this->statusCode());
        self::assertSame('EXPERIENCE_NOT_FOUND', $this->json()['error']['code']);
    }

    public function testMalformedIdReturnsUnprocessable(): void
    {
        $this->get('/experiences/not-a-ulid');

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());
        self::assertSame('INVALID_ULID', $this->json()['error']['code']);
    }
}
