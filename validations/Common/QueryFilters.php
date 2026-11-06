<?php

declare(strict_types=1);

namespace App\Validations\Common;

use App\Constants\Common\RegexPatterns;

/** Lenient parsing for list filters: invalid values are ignored instead of failing the page. */
final class QueryFilters
{
    public static function text(array $query, string $key, int $maxLength = 80): string
    {
        return mb_substr(trim((string) ($query[$key] ?? '')), 0, $maxLength);
    }

    public static function date(array $query, string $key): ?string
    {
        $value = (string) ($query[$key] ?? '');
        if (preg_match(RegexPatterns::DATE_ISO, $value) !== 1) {
            return null;
        }
        [$year, $month, $day] = array_map('intval', explode('-', $value));
        return checkdate($month, $day, $year) ? $value : null;
    }

    public static function code(array $query, string $key): string
    {
        $value = strtoupper(trim((string) ($query[$key] ?? '')));
        return preg_match(RegexPatterns::RECORD_CODE, $value) === 1 ? $value : '';
    }

    /**
     * @template T of \BackedEnum
     * @param class-string<T> $enum
     * @return T|null
     */
    public static function enum(array $query, string $key, string $enum): ?\BackedEnum
    {
        return $enum::tryFrom((string) ($query[$key] ?? ''));
    }
}
