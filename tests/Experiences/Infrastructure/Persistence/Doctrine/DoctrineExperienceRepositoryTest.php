<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Exception\ExperienceNotFoundException;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use App\Experiences\Infrastructure\Persistence\Doctrine\DoctrineExperienceRepository;
use App\Tests\Shared\Infrastructure\Persistence\Doctrine\DoctrineTestCase;

final class DoctrineExperienceRepositoryTest extends DoctrineTestCase
{
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FD2';

    private DoctrineExperienceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctrineExperienceRepository($this->entityManager);
    }

    public function testItPersistsAndRetrievesAnExperience(): void
    {
        $this->repository->save(Experience::register(
            ExperienceId::of(self::EXPERIENCE_ID),
            ProviderId::of(self::PROVIDER_ID),
            'Kayak sunset tour',
            'A guided kayak tour along the coast at sunset.',
        ));
        $this->entityManager->flush();
        $this->entityManager->clear();

        $experience = $this->repository->get(ExperienceId::of(self::EXPERIENCE_ID));

        self::assertSame(self::EXPERIENCE_ID, $experience->id()->value());
        self::assertSame(self::PROVIDER_ID, $experience->providerId()->value());
        self::assertSame('Kayak sunset tour', $experience->title());
        self::assertSame('A guided kayak tour along the coast at sunset.', $experience->description());
    }

    public function testGetThrowsWhenExperienceDoesNotExist(): void
    {
        $this->expectException(ExperienceNotFoundException::class);

        $this->repository->get(ExperienceId::of(self::EXPERIENCE_ID));
    }
}
