<?php declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use App\Shared\Domain\ValueObject\Ulid;
use DateTimeImmutable;

abstract class Entity
{
    private Ulid $id;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    protected function __construct(Ulid $id)
    {
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): Ulid
    {
        return $this->id;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
