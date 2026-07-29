<?php declare(strict_types=1);

namespace App\Experiences\Domain\Repository;

use App\Experiences\Domain\Experience;
use App\Experiences\Domain\ValueObject\ExperienceId;

interface ExperienceRepositoryInterface
{
    public function save(Experience $experience): void;

    public function get(ExperienceId $id): Experience;
}
