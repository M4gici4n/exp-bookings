<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;
use App\Shared\Domain\Validation\ValidationError;

final class ValidationFailedException extends DomainException
{
    /** @param ValidationError[] $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct(sprintf('Validation failed with %d error(s).', count($errors)));
    }

    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::InvalidArgument;
    }

    public function errorCode(): string
    {
        return 'VALIDATION_FAILED';
    }

    /** @return ValidationError[] */
    public function errors(): array
    {
        return $this->errors;
    }
}
