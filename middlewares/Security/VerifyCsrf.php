<?php

declare(strict_types=1);

namespace App\Middlewares\Security;

use App\Constants\Common\ErrorCodes;
use App\Exceptions\Common\AppException;
use App\Utilities\Http\Request;
use App\Utilities\Security\Csrf;

/** Every state-changing request must carry the session CSRF token (header or form field). */
final class VerifyCsrf
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, array $params): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return;
        }
        $token = $request->header(Csrf::HEADER) ?? $request->input(Csrf::FIELD);
        if (!Csrf::isValid(is_string($token) ? $token : null)) {
            throw new AppException(ErrorCodes::CSRF_MISMATCH, 'Your session expired. Refresh the page and try again.', 403);
        }
    }
}
