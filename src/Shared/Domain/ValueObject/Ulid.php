<?php declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUlidException;

class Ulid
{
    private const ULID_PATTERN = '/^[0-9A-HJKMNP-TV-Z]{26}$/';

    private string $value;

    final private function __construct(string $value)
    {
        $value = strtoupper($value);

        if (preg_match(self::ULID_PATTERN, $value) !== 1) {
            throw InvalidUlidException::withValue($value);
        }

        $this->value = $value;
    }

    public static function of(string $value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
