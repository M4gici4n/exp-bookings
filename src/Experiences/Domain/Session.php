<?php declare(strict_types=1);

namespace App\Experiences\Domain;

use App\Experiences\Domain\Event\SessionScheduled;
use App\Experiences\Domain\Exception\InvalidSeatCountException;
use App\Experiences\Domain\Exception\InvalidSessionCapacityException;
use App\Experiences\Domain\Exception\InvalidSessionPriceException;
use App\Experiences\Domain\Exception\NotEnoughSeatsException;
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
    private int $availableSeats;
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
        $this->availableSeats = $maxCapacity;
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

    public function reserve(int $seats): void
    {
        $this->guardPositiveSeats($seats);

        if ($seats > $this->availableSeats) {
            throw NotEnoughSeatsException::requested($seats, $this->availableSeats);
        }

        $this->availableSeats -= $seats;
    }

    public function release(int $seats): void
    {
        $this->guardPositiveSeats($seats);

        if ($this->availableSeats + $seats > $this->maxCapacity) {
            throw new LogicException('Releasing seats would exceed the session capacity.');
        }

        $this->availableSeats += $seats;
    }

    public function priceFor(int $seats): Money
    {
        $this->guardPositiveSeats($seats);

        return $this->price->multiply($seats);
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

    public function availableSeats(): int
    {
        return $this->availableSeats;
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

    private function guardPositiveSeats(int $seats): void
    {
        if ($seats < 1) {
            throw InvalidSeatCountException::of($seats);
        }
    }
}
