<?php declare(strict_types=1);

namespace App\Tests\Experiences\Infrastructure\InMemory;

use App\Experiences\Domain\Exception\ExperienceNotFoundException;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;

final class InMemoryExperienceRepository implements ExperienceRepositoryInterface
{
    /** @var array<string, Experience> */
    private array $experiences = [];

    public function save(Experience $experience): void
    {
        $this->experiences[$experience->id()->value()] = $experience;
    }

    public function get(ExperienceId $id): Experience
    {
        return $this->experiences[$id->value()] ?? throw ExperienceNotFoundException::withId($id);
    }

    public function all(): array
    {
        return array_values($this->experiences);
    }
}
