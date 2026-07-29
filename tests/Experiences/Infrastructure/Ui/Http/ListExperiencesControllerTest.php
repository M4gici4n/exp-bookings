<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Ui\Http;

use App\Tests\Shared\Infrastructure\Ui\Http\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ListExperiencesControllerTest extends ApiTestCase
{
    private const string PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    public function testListsExperiences(): void
    {
        $first = $this->createExperience('City tour');
        $second = $this->createExperience('Wine tasting');

        $this->get('/experiences');

        self::assertSame(Response::HTTP_OK, $this->statusCode());

        $ids = array_column($this->json()['data'], 'id');
        self::assertContains($first, $ids);
        self::assertContains($second, $ids);
    }

    private function createExperience(string $title): string
    {
        $this->postJson('/experiences', [
            'providerId' => self::PROVIDER_ID,
            'title' => $title,
            'description' => 'A nice experience.',
        ]);

        return $this->json()['data']['id'];
    }
}
