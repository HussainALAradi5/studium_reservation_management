<?php

declare(strict_types=1);

namespace App\Middlewares\Errors;

use App\Config\App\AppConfig;
use App\Constants\Common\ErrorCodes;
use App\Exceptions\Common\AppException;
use App\Utilities\Http\Redirect;
use App\Utilities\Http\Request;
use App\Utilities\Logging\Logger;
use App\Utilities\Responses\ApiResponse;
use App\Utilities\Views\View;

/** Maps every exception to the JSON error shape (API) or an error page (HTML). */
final class ErrorHandler
{

    public static function handle(\Throwable $error, Request $request): void
    {
        if ($error instanceof AppException) {
            self::respond($request, $error->errorCode, $error->getMessage(), $error->status, $error->details);
            return;
        }

        self::log($error, $request);
        $message = AppConfig::isDebug() ? $error->getMessage() : 'Something went wrong. Please try again.';
        self::respond($request, ErrorCodes::SERVER_ERROR, $message, 500);
    }

    private static function respond(Request $request, string $code, string $message, int $status, array $details = []): void
    {
        if ($request->isApi()) {
            ApiResponse::error($code, $message, $status, $details);
            return;
        }
        if ($status === 401) {
            Redirect::to('/login', ['next' => $request->path()]);
        }
        http_response_code($status);
        try {
            View::render('errors/error', ['title' => "Error $status", 'status' => $status, 'message' => $message, 'currentPath' => $request->path()]);
        } catch (\Throwable) {
            echo '<h1>' . e($status) . '</h1><p>' . e($message) . '</p>';
        }
    }

    /** Logs the error without request bodies, so passwords and tokens never reach the log. */
    private static function log(\Throwable $error, Request $request): void
    {
        Logger::error($request->method() . ' ' . $request->path(), $error);
    }
}
