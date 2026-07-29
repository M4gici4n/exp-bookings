<?php declare(strict_types=1);

namespace App\Tests\Experiences\Domain;

use App\Experiences\Domain\Event\ExperienceRegistered;
use App\Experiences\Domain\Exception\InvalidExperienceTitleException;
use App\Experiences\Domain\Experience;
use App\Experiences\Domain\ValueObject\ExperienceId;
use App\Experiences\Domain\ValueObject\ProviderId;
use PHPUnit\Framework\TestCase;

final class ExperienceTest extends TestCase
{
    private const EXPERIENCE_ID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';
    private const PROVIDER_ID = '01ARZ3NDEKTSV4RRFFQ69G5FB0';
    private const TITLE = 'Kayak sunset tour';
    private const DESCRIPTION = 'A guided kayak tour along the coast at sunset.';

    public function testItRegistersAnExperience(): void
    {
        $experience = $this->registerExperience();

        self::assertSame(self::EXPERIENCE_ID, $experience->id()->value());
        self::assertSame(self::PROVIDER_ID, $experience->providerId()->value());
        self::assertSame(self::TITLE, $experience->title());
        self::assertSame(self::DESCRIPTION, $experience->description());
    }

    public function testItRecordsAnExperienceRegisteredEvent(): void
    {
        $experience = $this->registerExperience();

        $events = $experience->pullEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(ExperienceRegistered::class, $events[0]);
        self::assertTrue($events[0]->experienceId()->equals(ExperienceId::of(self::EXPERIENCE_ID)));
        self::assertTrue($events[0]->providerId()->equals(ProviderId::of(self::PROVIDER_ID)));
    }

    public function testItTrimsTheTitle(): void
    {
        $experience = $this->registerExperience('  Kayak sunset tour  ');

        self::assertSame('Kayak sunset tour', $experience->title());
    }

    public function testItRejectsAnEmptyTitle(): void
    {
        $this->expectException(InvalidExperienceTitleException::class);

        $this->registerExperience('   ');
    }

    private function registerExperience(
        string $title = self::TITLE,
        string $description = self::DESCRIPTION,
    ): Experience {
        return Experience::register(
            ExperienceId::of(self::EXPERIENCE_ID),
            ProviderId::of(self::PROVIDER_ID),
            $title,
            $description,
        );
    }
}
