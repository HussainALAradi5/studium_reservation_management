<?php

declare(strict_types=1);

namespace App\Utilities\Security;

/** Per-session CSRF token, sent by AJAX in the X-CSRF-Token header. */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    public const HEADER = 'X-CSRF-Token';
    public const FIELD = '_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }
        return $token;
    }

    public static function isValid(?string $token): bool
    {
        $expected = Session::get(self::SESSION_KEY);
        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }
}
