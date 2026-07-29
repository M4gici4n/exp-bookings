<?php declare(strict_types=1);

namespace App\Shared\Domain\Validation;

use App\Shared\Domain\Validation\ValidationError;

final class ValidationErrors
{
    /** @var ValidationError[] */
    private array $errors = [];

    public function add(ValidationError $error): void
    {
        $this->errors[] = $error;
    }

    public function isEmpty(): bool
    {
        return $this->errors === [];
    }

    /** @return ValidationError[] */
    public function toArray(): array
    {
        return $this->errors;
    }
}
