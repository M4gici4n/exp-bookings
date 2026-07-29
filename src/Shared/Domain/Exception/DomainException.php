<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\Exception\ErrorStatus;
use ReflectionClass;
use RuntimeException;

abstract class DomainException extends RuntimeException
{
    public function errorStatus(): ErrorStatus
    {
        return ErrorStatus::Internal;
    }

    public function errorCode(): string
    {
        $shortName = (new ReflectionClass($this))->getShortName();
        $shortName = preg_replace('/Exception$/', '', $shortName);

        return strtoupper(preg_replace('/(?<!^)(?=[A-Z])/', '_', $shortName));
    }
}
