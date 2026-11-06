<?php

declare(strict_types=1);

namespace App\Middlewares\Security;

use App\Utilities\Http\Request;

/** Clickjacking, sniffing, referrer, and content-security headers on every response. */
final class SecurityHeaders
{
    private static ?string $nonce = null;

    public static function nonce(): string
    {
        return self::$nonce ??= base64_encode(random_bytes(16));
    }

    public function handle(Request $request, array $params): void
    {
        $nonce = self::nonce();
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
        header('Content-Security-Policy: ' . implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-$nonce' https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https://*.tile.openstreetmap.org https://tile.openstreetmap.org https://unpkg.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
        ]));
    }
}
