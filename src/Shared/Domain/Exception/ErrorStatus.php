<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

enum ErrorStatus: string
{
    case NotFound = 'NOT_FOUND';
    case Conflict = 'CONFLICT';
    case InvalidArgument = 'INVALID_ARGUMENT';
    case Internal = 'INTERNAL';
}
