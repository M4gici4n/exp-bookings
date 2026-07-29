<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine\Type;

use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractUlidType;

final class SessionIdType extends AbstractUlidType
{
    public const NAME = 'session_id';

    protected function targetClass(): string
    {
        return SessionId::class;
    }
}
