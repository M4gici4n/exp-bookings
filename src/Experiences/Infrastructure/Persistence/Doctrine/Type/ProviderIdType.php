<?php declare(strict_types=1);

namespace App\Experiences\Infrastructure\Persistence\Doctrine\Type;

use App\Experiences\Domain\ValueObject\ProviderId;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractUlidType;

final class ProviderIdType extends AbstractUlidType
{
    public const NAME = 'provider_id';

    protected function targetClass(): string
    {
        return ProviderId::class;
    }
}
