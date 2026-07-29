<?php declare(strict_types=1);

namespace App\Tests\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Ulid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UlidTest extends TestCase
{
    private const VALID_ULID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    public function testItCreatesUlidFromValidString(): void
    {
        $ulid = Ulid::of(self::VALID_ULID);

        self::assertSame(self::VALID_ULID, $ulid->value());
    }

    public function testItNormalizesToUppercase(): void
    {
        $ulid = Ulid::of(strtolower(self::VALID_ULID));

        self::assertSame(self::VALID_ULID, $ulid->value());
    }

    public function testItRejectsStringWithWrongLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Ulid::of('TOOSHORT');
    }

    public function testItRejectsStringWithInvalidCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Ulid::of('01ARZ3NDEKTSV4RRFFQ69G5FAI');
    }

    public function testEqualsComparesByValue(): void
    {
        $a = Ulid::of(self::VALID_ULID);
        $b = Ulid::of(self::VALID_ULID);

        self::assertTrue($a->equals($b));
    }
}
