<?php declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use App\Shared\Domain\Entity\Entity;
use App\Shared\Domain\Event\RecordsEvents;

abstract class AggregateRoot extends Entity
{
    use RecordsEvents;
}
