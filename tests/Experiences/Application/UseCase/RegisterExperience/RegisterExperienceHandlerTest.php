<?php declare(strict_types=1);

namespace App\Tests\Experiences\Application\UseCase\RegisterExperience;

use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceCommand;
use App\Experiences\Application\UseCase\RegisterExperience\RegisterExperienceHandler;
use App\Experiences\Domain\Event\ExperienceRegistered;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Tests\Experiences\Infrastructure\InMemory\InMemoryExperienceRepository;
use PHPUnit\Framework\TestCase;

final class RegisterExperienceHandlerTest extends TestCase
{
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';

    public function testItRegistersAnExperience(): void
    {
        $experiences = new InMemoryExperienceRepository();
        $handler = new RegisterExperienceHandler($experiences);

        ($handler)($this->command());

        $saved = $experiences->get(ExperienceId::of(self::EXPERIENCE_ID));
        self::assertSame(self::PROVIDER_ID, $saved->providerId()->value());
        self::assertSame('Kayak sunset tour', $saved->title());
    }

    public function testItRecordsAnExperienceRegisteredEvent(): void
    {
        $experiences = new InMemoryExperienceRepository();
        $handler = new RegisterExperienceHandler($experiences);

        ($handler)($this->command());

        $events = $experiences->get(ExperienceId::of(self::EXPERIENCE_ID))->pullEvents();

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
