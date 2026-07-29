<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\GetExperience;

use App\Experiences\Application\Query\GetExperience\ExperienceView;
use App\Experiences\Application\Query\GetExperience\GetExperienceQuery;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class GetExperienceHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
    ) {}

    public function __invoke(GetExperienceQuery $query): ExperienceView
    {
        $experience = $this->experiences->get(ExperienceId::of($query->experienceId));

        return ExperienceView::fromEntity($experience);
    }
}
