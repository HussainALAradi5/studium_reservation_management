<?php

declare(strict_types=1);

namespace App\Utilities\Responses;

/** The single JSON shape for every API endpoint: {success, data, meta} or {success, error{code, message, details}}. */
final class ApiResponse
{
    public static function success(mixed $data = null, int $status = 200, array $meta = []): void
    {
        $payload = ['success' => true, 'data' => $data];
        if ($meta !== []) {
            $payload['meta'] = $meta;
        }
        self::send($payload, $status);
    }

    public static function error(string $code, string $message, int $status, array $details = []): void
    {
        $error = ['code' => $code, 'message' => $message];
        if ($details !== []) {
            $error['details'] = $details;
        }
        self::send(['success' => false, 'error' => $error], $status);
    }

    private static function send(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
