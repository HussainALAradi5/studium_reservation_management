<?php

declare(strict_types=1);

namespace App\Config\App;

/** Typed application settings built from the environment. */
final class AppConfig
{
    public static function name(): string
    {
        return Env::string('APP_NAME', 'Studium');
    }

    public static function isProduction(): bool
    {
        return Env::string('APP_ENV', 'development') === 'production';
    }

    public static function isDebug(): bool
    {
        return Env::bool('APP_DEBUG') && !self::isProduction();
    }

    public static function timezone(): string
    {
        return Env::string('APP_TIMEZONE', 'Asia/Bahrain');
    }

    public static function currency(): string
    {
        return Env::string('APP_CURRENCY', 'BHD');
    }

    /** URL prefix of the app, e.g. "/studium_reservation_management" (no trailing slash). */
    public static function basePath(): string
    {
        $configured = Env::string('APP_BASE_PATH');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return rtrim($script === '.' ? '' : $script, '/');
    }

    public static function sessionIdleMinutes(): int
    {
        return Env::int('SESSION_IDLE_MINUTES', 120);
    }

    /** @return array{lat: float, lng: float, zoom: int, tileUrl: string} */
    public static function map(): array
    {
        return [
            'lat' => Env::float('MAP_DEFAULT_LAT', 26.0667),
            'lng' => Env::float('MAP_DEFAULT_LNG', 50.5577),
            'zoom' => Env::int('MAP_DEFAULT_ZOOM', 11),
            'tileUrl' => Env::string('MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
        ];
    }
}
