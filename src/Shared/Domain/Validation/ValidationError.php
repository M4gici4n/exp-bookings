<?php declare(strict_types=1);

namespace App\Shared\Domain\Validation;

use App\Shared\Domain\Validation\ValidationCode;

final class ValidationError
{
    public function __construct(
        public readonly ValidationCode $code,
        public readonly string $target,
        public readonly string $message,
    ) {}
}
