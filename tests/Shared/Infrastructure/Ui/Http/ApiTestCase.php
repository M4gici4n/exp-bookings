<?php declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Ui\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();
        parent::tearDown();
    }

    /** @param array<string, mixed> $payload */
    protected function postJson(string $uri, array $payload): void
    {
        $this->postRaw($uri, (string) json_encode($payload));
    }

    protected function postRaw(string $uri, string $body): void
    {
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], $body);
    }

    protected function post(string $uri): void
    {
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json']);
    }

    protected function get(string $uri): void
    {
        $this->client->request('GET', $uri);
    }

    protected function statusCode(): int
    {
        return $this->client->getResponse()->getStatusCode();
    }

    /** @return array<string, mixed> */
    protected function json(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true);
    }
}
