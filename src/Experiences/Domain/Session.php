<?php declare(strict_types=1);

namespace App\Experiences\Domain;

use App\Experiences\Domain\Event\SessionScheduled;
use App\Experiences\Domain\Exception\InvalidSpotCountException;
use App\Experiences\Domain\Exception\InvalidSessionCapacityException;
use App\Experiences\Domain\Exception\InvalidSessionPriceException;
use App\Experiences\Domain\Exception\NotEnoughSpotsException;
use App\Experiences\Domain\Exception\PastSessionDateException;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Shared\Domain\Entity\AggregateRoot;
use App\Shared\Domain\ValueObject\Money;
use DateTimeImmutable;
use LogicException;

final class Session extends AggregateRoot
{
    private SessionId $id;
    private ExperienceId $experienceId;
    private DateTimeImmutable $startsAt;
    private int $maxCapacity;
    private int $availableSpots;
    private Money $price;

    private function __construct(
        SessionId $id,
        ExperienceId $experienceId,
        DateTimeImmutable $startsAt,
        int $maxCapacity,
        Money $price,
    ) {
        parent::__construct();

        $this->id = $id;
        $this->experienceId = $experienceId;
        $this->startsAt = $startsAt;
        $this->maxCapacity = $maxCapacity;
        $this->availableSpots = $maxCapacity;
        $this->price = $price;
    }

    public static function schedule(
        SessionId $id,
        ExperienceId $experienceId,
        DateTimeImmutable $startsAt,
        int $maxCapacity,
        Money $price,
        DateTimeImmutable $now,
    ): self {
        self::guardSchedule($maxCapacity, $price, $startsAt, $now);

        $session = new self($id, $experienceId, $startsAt, $maxCapacity, $price);
        $session->recordEvent(new SessionScheduled($id, $experienceId, $startsAt));

        return $session;
    }

    public function reserve(int $spots): void
    {
        $this->guardPositiveSpots($spots);

        if ($spots > $this->availableSpots) {
            throw NotEnoughSpotsException::requested($spots, $this->availableSpots);
        }

        $this->availableSpots -= $spots;
    }

    public function release(int $spots): void
    {
        $this->guardPositiveSpots($spots);

        if ($this->availableSpots + $spots > $this->maxCapacity) {
            throw new LogicException('Releasing spots would exceed the session capacity.');
        }

        $this->availableSpots += $spots;
    }

    public function totalPriceFor(int $spots): Money
    {
        $this->guardPositiveSpots($spots);

        return $this->price->multiply($spots);
    }

    public function hasStarted(DateTimeImmutable $now): bool
    {
        return $now >= $this->startsAt;
    }

    public function id(): SessionId
    {
        return $this->id;
    }

    public function experienceId(): ExperienceId
    {
        return $this->experienceId;
    }

    public function startsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function maxCapacity(): int
    {
        return $this->maxCapacity;
    }

    public function availableSpots(): int
    {
        return $this->availableSpots;
    }

    public function price(): Money
    {
        return $this->price;
    }

    private static function guardSchedule(
        int $maxCapacity,
        Money $price,
        DateTimeImmutable $startsAt,
        DateTimeImmutable $now,
    ): void {
        if ($maxCapacity < 1) {
            throw InvalidSessionCapacityException::of($maxCapacity);
        }

        if ($price->amount() < 0) {
            throw InvalidSessionPriceException::negative($price->amount());
        }

        if ($startsAt <= $now) {
            throw PastSessionDateException::at($startsAt);
        }
    }

    private function guardPositiveSpots(int $spots): void
    {
        if ($spots < 1) {
            throw InvalidSpotCountException::of($spots);
        }
    }
}
