<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Shared\Domain\ValueObject\Ulid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

final class SymfonyUlidGenerator implements UlidGeneratorInterface
{
    public function generate(): Ulid
    {
        return Ulid::of((new SymfonyUlid())->toBase32());
    }
}
