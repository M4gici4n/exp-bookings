<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use DateTimeImmutable;

final class PastSessionDateException extends DomainException
{
    public static function at(DateTimeImmutable $date): self
    {
        return new self("Cannot schedule a session in the past: {$date->format(DateTimeImmutable::ATOM)}.");
    }
}
