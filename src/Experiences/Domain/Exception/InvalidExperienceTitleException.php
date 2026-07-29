<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;

final class InvalidExperienceTitleException extends DomainException
{
    public static function empty(): self
    {
        return new self('Experience title cannot be empty.');
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::InvalidArgument;
    }
}
