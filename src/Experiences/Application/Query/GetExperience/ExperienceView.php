<?php declare(strict_types=1);

namespace App\Experiences\Application\Query\GetExperience;

use App\Experiences\Domain\Experience;

final readonly class ExperienceView
{
    public function __construct(
        public string $id,
        public string $providerId,
        public string $title,
        public string $description,
    ) {}

    public static function fromEntity(Experience $experience): self
    {
        return new self(
            $experience->id()->value(),
            $experience->providerId()->value(),
            $experience->title(),
            $experience->description(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'providerId' => $this->providerId,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
