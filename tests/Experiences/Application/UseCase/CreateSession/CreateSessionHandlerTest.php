<?php declare(strict_types=1);

namespace App\Tests\Experiences\Application\UseCase\CreateSession;

use App\Experiences\Application\UseCase\CreateSession\CreateSessionCommand;
use App\Experiences\Application\UseCase\CreateSession\CreateSessionHandler;
use App\Experiences\Domain\Exception\ExperienceNotFoundException;
use App\Experiences\Domain\Exception\PastSessionDateException;
use App\Experiences\Domain\Exception\SessionAlreadyExistsForDayException;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use App\Experiences\Domain\ValueObject\SessionId;
use App\Tests\Experiences\Infrastructure\InMemory\InMemoryExperienceRepository;
use App\Tests\Experiences\Infrastructure\InMemory\InMemorySessionRepository;
use App\Tests\Shared\Infrastructure\Service\FixedClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CreateSessionHandlerTest extends TestCase
{
    private const SESSION_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const SESSION_ID_2 = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FC1';
    private const PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FD2';
    private const NOW = '2026-07-29 12:00:00';
    private const STARTS_AT = '2026-08-15 18:00:00';

    private InMemoryExperienceRepository $experiences;
    private InMemorySessionRepository $sessions;
    private CreateSessionHandler $handler;

    protected function setUp(): void
    {
        $this->experiences = new InMemoryExperienceRepository();
        $this->sessions = new InMemorySessionRepository();
        $this->handler = new CreateSessionHandler(
            $this->experiences,
            $this->sessions,
            new FixedClock(new DateTimeImmutable(self::NOW)),
        );
    }

    public function testItCreatesASession(): void
    {
        $this->givenExistingExperience();

        ($this->handler)($this->command());

        $session = $this->sessions->get(SessionId::of(self::SESSION_ID));
        self::assertSame(self::EXPERIENCE_ID, $session->experienceId()->value());
        self::assertSame(10, $session->maxCapacity());
        self::assertSame(10, $session->availableSeats());
    }

    public function testItRejectsWhenExperienceDoesNotExist(): void
    {
        $this->expectException(ExperienceNotFoundException::class);

        ($this->handler)($this->command());
    }

    public function testItRejectsASecondSessionOnTheSameDay(): void
    {
        $this->givenExistingExperience();
        ($this->handler)($this->command());

        $this->expectException(SessionAlreadyExistsForDayException::class);

        ($this->handler)($this->command(sessionId: self::SESSION_ID_2, startsAt: '2026-08-15 20:00:00'));
    }

    public function testItRejectsAPastDate(): void
    {
        $this->givenExistingExperience();

        $this->expectException(PastSessionDateException::class);

        ($this->handler)($this->command(startsAt: '2026-07-28 10:00:00'));
    }

    private function givenExistingExperience(): void
    {
        $this->experiences->save(Experience::register(
            ExperienceId::of(self::EXPERIENCE_ID),
            ProviderId::of(self::PROVIDER_ID),
            'Kayak sunset tour',
            'A guided kayak tour along the coast at sunset.',
        ));
    }

    private function command(
        string $sessionId = self::SESSION_ID,
        string $startsAt = self::STARTS_AT,
    ): CreateSessionCommand {
        return new CreateSessionCommand(
            $sessionId,
            self::EXPERIENCE_ID,
            $startsAt,
            10,
            2500,
            'EUR',
        );
    }
}
