<?php

declare(strict_types=1);

namespace App\Utilities\Http;

final class Redirect
{
    /** Sends a redirect to an app path and ends the request. */
    public static function to(string $path, array $query = []): never
    {
        header('Location: ' . url($path, $query), true, 302);
        exit;
    }
}
