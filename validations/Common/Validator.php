<?php

declare(strict_types=1);

namespace App\Validations\Common;

use App\Constants\Common\RegexPatterns;
use App\Exceptions\Common\ValidationException;

/**
 * Reusable input validator. Each rule reads one field, records an error or the cleaned value,
 * and returns $this; validated() throws ValidationException with every field error at once.
 */
final class Validator
{
    private array $errors = [];
    private array $clean = [];

    public function __construct(private readonly array $input)
    {
    }

    public function string(string $field, string $label, int $min = 1, int $max = 255, bool $required = true, ?string $pattern = null): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        $length = mb_strlen($value);
        if ($length < $min || $length > $max) {
            return $this->fail($field, "$label must be between $min and $max characters.");
        }
        if ($pattern !== null && preg_match($pattern, $value) !== 1) {
            return $this->fail($field, "$label has an invalid format.");
        }
        return $this->pass($field, $value);
    }

    public function email(string $field, string $label = 'Email', bool $required = true): self
    {
        $value = strtolower($this->raw($field));
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        if (strlen($value) > 160 || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return $this->fail($field, 'Enter a valid email address.');
        }
        return $this->pass($field, $value);
    }

    public function integer(string $field, string $label, int $min, int $max, bool $required = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < $min || (int) $value > $max) {
            return $this->fail($field, "$label must be a whole number between $min and $max.");
        }
        return $this->pass($field, (int) $value);
    }

    public function decimal(string $field, string $label, float $min, float $max, bool $required = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        if (!is_numeric($value) || (float) $value < $min || (float) $value > $max) {
            return $this->fail($field, "$label must be a number between $min and $max.");
        }
        return $this->pass($field, (float) $value);
    }

    /** @param class-string<\BackedEnum> $enum */
    public function enum(string $field, string $label, string $enum, bool $required = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        $case = $enum::tryFrom($value);
        return $case === null ? $this->fail($field, "Choose a valid $label.") : $this->pass($field, $case);
    }

    public function inList(string $field, string $label, array $allowed, bool $required = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        $match = array_values(array_filter($allowed, static fn ($item) => (string) $item === $value));
        return $match === [] ? $this->fail($field, "Choose a valid $label.") : $this->pass($field, $match[0]);
    }

    public function date(string $field, string $label, bool $required = true, bool $allowFuture = true, bool $allowPast = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            return $this->fail($field, "$label must be a valid date.");
        }
        $today = new \DateTimeImmutable('today');
        if (!$allowFuture && $date > $today) {
            return $this->fail($field, "$label cannot be in the future.");
        }
        if (!$allowPast && $date < $today) {
            return $this->fail($field, "$label cannot be in the past.");
        }
        return $this->pass($field, $value);
    }

    public function time(string $field, string $label, bool $required = true): self
    {
        $value = $this->raw($field);
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        if (preg_match(RegexPatterns::TIME_HHMM, substr($value, 0, 5)) !== 1) {
            return $this->fail($field, "$label must be a valid time (HH:MM).");
        }
        return $this->pass($field, substr($value, 0, 5));
    }

    /** Accepts "Y-m-d H:i" or "Y-m-dTH:i" (datetime-local input). */
    public function dateTime(string $field, string $label, bool $required = true): self
    {
        $value = str_replace('T', ' ', $this->raw($field));
        if ($value === '') {
            return $this->missing($field, $label, $required);
        }
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i', substr($value, 0, 16));
        if ($parsed === false) {
            return $this->fail($field, "$label must be a valid date and time.");
        }
        return $this->pass($field, $parsed);
    }

    public function boolean(string $field): self
    {
        $value = $this->input[$field] ?? false;
        return $this->pass($field, in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true));
    }

    /** Adds a custom error (rules that need several fields or the database). */
    public function addError(string $field, string $message): self
    {
        return $this->fail($field, $message);
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->clean) && $this->clean[$field] !== null;
    }

    public function value(string $field): mixed
    {
        return $this->clean[$field] ?? null;
    }

    public function validated(): array
    {
        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }
        return $this->clean;
    }

    private function raw(string $field): string
    {
        $value = $this->input[$field] ?? '';
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function missing(string $field, string $label, bool $required): self
    {
        return $required ? $this->fail($field, "$label is required.") : $this->pass($field, null);
    }

    private function pass(string $field, mixed $value): self
    {
        $this->clean[$field] = $value;
        return $this;
    }

    private function fail(string $field, string $message): self
    {
        $this->errors[$field] ??= $message;
        $this->clean[$field] = null;
        return $this;
    }
}
