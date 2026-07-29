<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\ListSessions;

use App\Experiences\Application\Query\GetSession\SessionView;
use App\Experiences\Application\Query\ListSessions\ListSessionsQuery;
use App\Experiences\Domain\Repository\ExperienceRepositoryInterface;
use App\Experiences\Domain\Repository\SessionRepositoryInterface;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Shared\Application\Bus\Query\QueryHandlerInterface;

final class ListSessionsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ExperienceRepositoryInterface $experiences,
        private readonly SessionRepositoryInterface $sessions,
    ) {}

    /** @return SessionView[] */
    public function __invoke(ListSessionsQuery $query): array
    {
        $experienceId = ExperienceId::of($query->experienceId);

        $this->experiences->get($experienceId);

        $views = [];

        foreach ($this->sessions->allByExperience($experienceId) as $session) {
            $views[] = SessionView::fromEntity($session);
        }

        return $views;
    }
}
