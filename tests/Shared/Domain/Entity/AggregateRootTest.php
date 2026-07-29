<?php declare(strict_types=1);

namespace App\Tests\Shared\Domain\Entity;

use App\Shared\Domain\Entity\AggregateRoot;
use App\Shared\Domain\Event\AbstractDomainEvent;
use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\ValueObject\Ulid;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    public function testItRecordsAndPullsEvents(): void
    {
        $root = $this->newAggregateRoot();
        $event = $this->newEvent();

        $root->fire($event);
        $pulled = $root->pullEvents();

        self::assertSame([$event], $pulled);
    }

    public function testPullingEventsClearsTheRecordedEvents(): void
    {
        $root = $this->newAggregateRoot();
        $root->fire($this->newEvent());

        $root->pullEvents();

        self::assertSame([], $root->pullEvents());
    }

    private function newAggregateRoot(): object
    {
        return new class (Ulid::of('01ARZ3NDEKTSV4RRFFQ69G5FAV')) extends AggregateRoot {
            public function __construct(Ulid $id)
            {
                parent::__construct($id);
            }

            public function fire(DomainEventInterface $event): void
            {
                $this->recordEvent($event);
            }
        };
    }

    private function newEvent(): DomainEventInterface
    {
        return new class extends AbstractDomainEvent {};
    }
}
