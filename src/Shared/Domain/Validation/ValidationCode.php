<?php declare(strict_types=1);

namespace App\Shared\Domain\Validation;

enum ValidationCode: string
{
    case FieldRequired = 'FIELD_REQUIRED';
    case InvalidFormat = 'INVALID_FORMAT';
}
