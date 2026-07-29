<?php declare(strict_types=1);

namespace App\Experiences\Application\UseCase\CreateSession;

final readonly class CreateSessionCommand
{
    public function __construct(
        public string $sessionId,
        public string $experienceId,
        public string $startsAt,
        public int $maxCapacity,
        public int $priceAmount,
        public string $priceCurrency,
    ) {}
}
