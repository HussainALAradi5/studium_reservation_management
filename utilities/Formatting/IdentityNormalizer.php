<?php

declare(strict_types=1);

namespace App\Utilities\Formatting;

use App\Constants\Common\RegexPatterns;

/**
 * Canonical forms for values that must be unique regardless of letter case or formatting:
 * usernames and emails are lower-case, phones lose separators, record codes are upper-case.
 * Stored values are always canonical, and the utf8mb4_unicode_ci unique indexes reject
 * case-only duplicates at the database level as a second guard.
 */
final class IdentityNormalizer
{
    public static function username(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    public static function email(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    public static function phone(string $value): string
    {
        return (string) preg_replace(RegexPatterns::PHONE_SEPARATORS, '', trim($value));
    }

    public static function code(string $value): string
    {
        return strtoupper(trim($value));
    }

    public static function text(string $value): string
    {
        return trim((string) preg_replace(RegexPatterns::WHITESPACE_RUN, ' ', $value));
    }

    /** Normalizes the identity fields present in a request body before validation. */
    public static function userInput(array $input): array
    {
        $rules = ['username' => 'username', 'email' => 'email', 'phone' => 'phone', 'identifier' => 'username', 'fullName' => 'text'];
        foreach ($rules as $field => $method) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $input[$field] = self::$method($input[$field]);
            }
        }
        return $input;
    }
}
