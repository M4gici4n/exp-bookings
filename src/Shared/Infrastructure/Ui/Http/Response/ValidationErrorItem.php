<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Ui\Http\Response;

final readonly class ValidationErrorItem
{
    public function __construct(
        public string $code,
        public string $target,
        public string $message,
    ) {}
}
