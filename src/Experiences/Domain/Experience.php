<?php declare(strict_types=1);

namespace App\Experiences\Domain;

use App\Experiences\Domain\Event\ExperienceRegistered;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use App\Shared\Domain\Entity\AggregateRoot;
use InvalidArgumentException;

final class Experience extends AggregateRoot
{
    private ExperienceId $id;
    private ProviderId $providerId;
    private string $title;
    private string $description;

    private function __construct(
        ExperienceId $id,
        ProviderId $providerId,
        string $title,
        string $description,
    ) {
        parent::__construct();

        $this->id = $id;
        $this->providerId = $providerId;
        $this->title = $this->guardTitle($title);
        $this->description = $description;
    }

    public static function register(
        ExperienceId $id,
        ProviderId $providerId,
        string $title,
        string $description,
    ): self {
        $experience = new self($id, $providerId, $title, $description);
        $experience->recordEvent(new ExperienceRegistered($id, $providerId));

        return $experience;
    }

    public function id(): ExperienceId
    {
        return $this->id;
    }

    public function providerId(): ProviderId
    {
        return $this->providerId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    private function guardTitle(string $title): string
    {
        $title = trim($title);

        if ($title === '') {
            throw new InvalidArgumentException('Experience title cannot be empty.');
        }

        return $title;
    }
}
