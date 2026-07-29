<?php declare(strict_types=1);

namespace App\Tests\Experiences\Application\UseCase\RegisterExperience;

use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceCommand;
use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceHandler;
use App\Experiences\Domain\Event\ExperienceRegistered;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Tests\Experiences\Infrastructure\InMemory\InMemoryExperienceRepository;
use App\Tests\Shared\Infrastructure\Event\InMemoryEventDispatcher;
use PHPUnit\Framework\TestCase;

final class RegisterExperienceHandlerTest extends TestCase
{
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';

    private InMemoryExperienceRepository $experiences;
    private InMemoryEventDispatcher $eventDispatcher;
    private RegisterExperienceHandler $handler;

    protected function setUp(): void
    {
        $this->experiences = new InMemoryExperienceRepository();
        $this->eventDispatcher = new InMemoryEventDispatcher();
        $this->handler = new RegisterExperienceHandler($this->experiences, $this->eventDispatcher);
    }

    public function testItRegistersAnExperience(): void
    {
        ($this->handler)($this->command());

        $saved = $this->experiences->get(ExperienceId::of(self::EXPERIENCE_ID));
        self::assertSame(self::PROVIDER_ID, $saved->providerId()->value());
        self::assertSame('Kayak sunset tour', $saved->title());
    }

    public function testItDispatchesAnExperienceRegisteredEvent(): void
    {
        ($this->handler)($this->command());

        $events = $this->eventDispatcher->dispatchedEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ExperienceRegistered::class, $events[0]);
    }

    private function command(): RegisterExperienceCommand
    {
        return new RegisterExperienceCommand(
            self::EXPERIENCE_ID,
            self::PROVIDER_ID,
            'Kayak sunset tour',
            'A guided kayak tour along the coast at sunset.',
        );
    }
}
