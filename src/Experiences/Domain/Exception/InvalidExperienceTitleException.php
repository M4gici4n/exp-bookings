<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InvalidExperienceTitleException extends DomainException
{
    public static function empty(): self
    {
        return new self('Experience title cannot be empty.');
    }
}
