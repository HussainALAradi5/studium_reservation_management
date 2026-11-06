<?php

declare(strict_types=1);

namespace App\Config\App;

/** Reads the .env file once and exposes typed values. */
final class Env
{
    private static array $values = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            self::$values[trim($key)] = trim(trim($value), "\"'");
        }
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::$values[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : (string) $value;
    }

    public static function int(string $key, int $default): int
    {
        $value = self::string($key);
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function float(string $key, float $default): float
    {
        $value = self::string($key);
        return is_numeric($value) ? (float) $value : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = strtolower(self::string($key));
        return $value === '' ? $default : in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}
