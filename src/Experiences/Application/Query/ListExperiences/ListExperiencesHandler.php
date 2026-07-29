<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\ListExperiences;

use App\Experiences\Application\Query\GetExperience\ExperienceView;
use App\Experiences\Application\Query\ListExperiences\ListExperiencesQuery;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class ListExperiencesHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
    ) {}

    /** @return ExperienceView[] */
    public function __invoke(ListExperiencesQuery $query): array
    {
        $views = [];

        foreach ($this->experiences->all() as $experience) {
            $views[] = ExperienceView::fromEntity($experience);
        }

        return $views;
    }
}
