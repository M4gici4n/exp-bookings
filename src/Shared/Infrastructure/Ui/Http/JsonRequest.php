<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Ui\Http;

use App\Shared\Domain\Exception\ValidationFailedException;
use App\Shared\Domain\Validation\ValidationCode;
use App\Shared\Domain\Validation\ValidationError;
use App\Shared\Domain\Validation\ValidationErrors;
use App\Shared\Infrastructure\Ui\Http\Error\MalformedRequestBodyException;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Request;

final class JsonRequest
{
    /** @var array<string, mixed> */
    private array $data;
    private ValidationErrors $errors;

    public function __construct(Request $request)
    {
        $content = trim($request->getContent());

        if ($content === '') {
            $this->data = [];
        } else {
            $decoded = json_decode($content, true);

            if (!is_array($decoded)) {
                throw new MalformedRequestBodyException(json_last_error_msg());
            }

            $this->data = $decoded;
        }

        $this->errors = new ValidationErrors();
    }

    public function requiredString(string $field): ?string
    {
        if (!array_key_exists($field, $this->data)) {
            $this->addRequired($field);

            return null;
        }

        $value = $this->data[$field];

        if (!is_string($value)) {
            $this->addInvalidFormat($field, 'a string');

            return null;
        }

        return $value;
    }

    public function requiredInt(string $field): ?int
    {
        if (!array_key_exists($field, $this->data)) {
            $this->addRequired($field);

            return null;
        }

        $value = $this->data[$field];

        if (!is_int($value)) {
            $this->addInvalidFormat($field, 'an integer');

            return null;
        }

        return $value;
    }

    public function requiredDateTimeString(string $field): ?string
    {
        $value = $this->requiredString($field);

        if ($value === null) {
            return null;
        }

        try {
            new DateTimeImmutable($value);
        } catch (Exception) {
            $this->addInvalidFormat($field, 'a valid date/time string');

            return null;
        }

        return $value;
    }

    public function validate(): void
    {
        if (!$this->errors->isEmpty()) {
            throw new ValidationFailedException($this->errors->toArray());
        }
    }

    private function addRequired(string $field): void
    {
        $this->errors->add(new ValidationError(
            ValidationCode::FieldRequired,
            $field,
            sprintf('Field "%s" is required.', $field),
        ));
    }

    private function addInvalidFormat(string $field, string $expected): void
    {
        $this->errors->add(new ValidationError(
            ValidationCode::InvalidFormat,
            $field,
            sprintf('Field "%s" must be %s.', $field, $expected),
        ));
    }
}
