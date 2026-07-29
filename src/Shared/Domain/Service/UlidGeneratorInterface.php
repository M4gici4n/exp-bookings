<?php declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\Ulid;

interface UlidGeneratorInterface
{
    public function generate(): Ulid;
}
