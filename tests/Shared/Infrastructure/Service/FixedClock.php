<?php declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\ClockInterface;
use DateTimeImmutable;

final class FixedClock implements ClockInterface
{
    public function __construct(private readonly DateTimeImmutable $now) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
