<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class CreateSessionControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const string UNKNOWN_EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FZZ';

    public function testCreatesSessionAndReturnsCreatedWithLocation(): void
    {
        $experienceId = $this->createExperience();

        $this->postJson("/experiences/{$experienceId}/sessions", $this->sessionPayload('+10 days'));

        self::assertSame(Response::HTTP_CREATED, $this->statusCode());

        $id = $this->json()['data']['id'];
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id);
        self::assertSame('/sessions/' . $id, $this->client->getResponse()->headers->get('Location'));
    }

    public function testSecondSessionOnSameDayReturnsConflict(): void
    {
        $experienceId = $this->createExperience();
        $sameDay = (new DateTimeImmutable('+10 days'))->format('Y-m-d');

        $this->postJson("/experiences/{$experienceId}/sessions", $this->sessionPayload($sameDay . 'T09:00:00'));
        self::assertSame(Response::HTTP_CREATED, $this->statusCode());

        $this->postJson("/experiences/{$experienceId}/sessions", $this->sessionPayload($sameDay . 'T18:00:00'));

        self::assertSame(Response::HTTP_CONFLICT, $this->statusCode());
        self::assertSame('SESSION_ALREADY_EXISTS_FOR_DAY', $this->json()['error']['code']);
    }

    public function testPastDateReturnsUnprocessable(): void
    {
        $experienceId = $this->createExperience();

        $this->postJson("/experiences/{$experienceId}/sessions", $this->sessionPayload('-1 day'));

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());
        self::assertSame('PAST_SESSION_DATE', $this->json()['error']['code']);
    }

    public function testInvalidDateStringReturnsValidationError(): void
    {
        $experienceId = $this->createExperience();

        $payload = $this->sessionPayload('+10 days');
        $payload['startsAt'] = 'not-a-date';

        $this->postJson("/experiences/{$experienceId}/sessions", $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());

        $error = $this->json()['error'];
        self::assertSame('VALIDATION_FAILED', $error['code']);
        self::assertSame('startsAt', $error['details']['errors'][0]['target']);
    }

    public function testNonIntegerCapacityReturnsValidationError(): void
    {
        $experienceId = $this->createExperience();

        $payload = $this->sessionPayload('+10 days');
        $payload['maxCapacity'] = 'ten';

        $this->postJson("/experiences/{$experienceId}/sessions", $payload);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->statusCode());
        self::assertSame('VALIDATION_FAILED', $this->json()['error']['code']);
    }

    public function testUnknownExperienceReturnsNotFound(): void
    {
        $this->postJson('/experiences/' . self::UNKNOWN_EXPERIENCE_ID . '/sessions', $this->sessionPayload('+10 days'));

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

    /** @return array<string, mixed> */
    private function sessionPayload(string $startsAt): array
    {
        return [
            'startsAt' => (new DateTimeImmutable($startsAt))->format(DateTimeImmutable::ATOM),
            'maxCapacity' => 20,
            'priceAmount' => 4500,
            'priceCurrency' => 'EUR',
        ];
    }
}
