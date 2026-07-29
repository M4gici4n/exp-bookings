<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class InvalidUlidException extends DomainException
{
    public static function withValue(string $value): self
    {
        return new self("Invalid ULID format: {$value}");
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::InvalidArgument;
    }
}
