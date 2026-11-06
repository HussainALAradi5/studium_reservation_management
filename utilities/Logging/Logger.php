<?php

declare(strict_types=1);

namespace App\Utilities\Logging;

/** Appends one line per event to storage/logs/app.log. Never pass passwords, tokens, or request bodies. */
final class Logger
{
    private const FILE = BASE_DIR . '/storage/logs/app.log';

    public static function error(string $context, \Throwable $error): void
    {
        self::write(sprintf('%s — %s: %s in %s:%d', $context, $error::class, $error->getMessage(), $error->getFile(), $error->getLine()));
    }

    public static function write(string $message): void
    {
        @file_put_contents(self::FILE, '[' . date('c') . '] ' . $message . "\n", FILE_APPEND | LOCK_EX);
    }
}
