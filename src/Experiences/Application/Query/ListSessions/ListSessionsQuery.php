<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\ListSessions;

final readonly class ListSessionsQuery
{
    public function __construct(
        public string $experienceId,
    ) {}
}
