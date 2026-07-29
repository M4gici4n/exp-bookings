<?php declare(strict_types=1);

namespace App\Experiences\Domain\Exception;

use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Exception\DomainException;

final class SessionAlreadyStartedException extends DomainException
{
    public static function withId(SessionId $id): self
    {
        return new self("Session {$id} has already started and cannot be booked.");
    }
}
