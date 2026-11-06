<?php

declare(strict_types=1);

namespace App\Utilities\Security;

use App\Constants\Common\RegexPatterns;
use App\Constants\Users\AuthRules;

final class PasswordHasher
{
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** At least the minimum length with one letter and one digit. */
    public static function isStrong(string $password): bool
    {
        return strlen($password) >= AuthRules::PASSWORD_MIN_LENGTH
            && preg_match(RegexPatterns::PASSWORD_LETTER, $password) === 1
            && preg_match(RegexPatterns::PASSWORD_DIGIT, $password) === 1;
    }
}
