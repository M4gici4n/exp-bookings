<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RegisterExperienceControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    public function testRegistersExperienceAndReturnsCreatedWithLocation(): void
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => 'Guided city tour',
            'description' => 'A two-hour walk through the old town.',
        ]);

        self::assertSame(Response::HTTP_CREATED, $this->statusCode());

        $id = $this->json()['data']['id'];
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id);
        self::assertSame('/experiences/' . $id, $this->client->getResponse()->headers->get('Location'));
    }

    public function testMissingFieldsReturnValidationError(): void
    {
        $this->postJson('/experiences', []);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());

        $error = $this->json()['error'];
        self::assertSame('VALIDATION_FAILED', $error['code']);

        $targets = array_column($error['details']['errors'], 'target');
        self::assertContains('providerId', $targets);
        self::assertContains('title', $targets);
        self::assertContains('description', $targets);
    }

    public function testMalformedJsonReturnsBadRequest(): void
    {
        $this->postRaw('/experiences', 'not a json body');

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->statusCode());
        self::assertSame('MALFORMED_REQUEST_BODY', $this->json()['error']['code']);
    }

    public function testInvalidProviderIdIsRejectedByTheDomain(): void
    {
        $this->postJson('/experiences', [
            'providerId' => 'not-a-ulid',
            'title' => 'Guided city tour',
            'description' => 'A walk.',
        ]);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());
        self::assertSame('INVALID_ULID', $this->json()['error']['code']);
    }
}
