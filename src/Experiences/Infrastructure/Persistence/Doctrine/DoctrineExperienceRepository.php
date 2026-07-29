<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine;

use App\Experiences\Domain\Experience;
use App\Experiences\Domain\Exception\ExperienceNotFoundException;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineExperienceRepository implements ExperienceRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(Experience $experience): void
    {
        $this->entityManager->persist($experience);
    }

    public function get(ExperienceId $id): Experience
    {
        return $this->entityManager->find(Experience::class, $id)
            ?? throw ExperienceNotFoundException::withId($id);
    }
}
