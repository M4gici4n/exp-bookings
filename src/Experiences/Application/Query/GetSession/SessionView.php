<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\GetSession;

use App\Experiences\Domain\Session;
use DateTimeImmutable;

final readonly class SessionView
{
    public function __construct(
        public string $id,
        public string $experienceId,
        public string $startsAt,
        public int $maxCapacity,
        public int $availableSpots,
        public int $priceAmount,
        public string $priceCurrency,
    ) {}

    public static function fromEntity(Session $session): self
    {
        return new self(
            $session->id()->value(),
            $session->experienceId()->value(),
            $session->startsAt()->format(DateTimeImmutable::ATOM),
            $session->maxCapacity(),
            $session->availableSpots(),
            $session->price()->amount(),
            $session->price()->currency()->value,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'experienceId' => $this->experienceId,
            'startsAt' => $this->startsAt,
            'maxCapacity' => $this->maxCapacity,
            'availableSpots' => $this->availableSpots,
            'price' => [
                'amount' => $this->priceAmount,
                'currency' => $this->priceCurrency,
            ],
        ];
    }
}
