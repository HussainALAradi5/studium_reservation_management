<?php

declare(strict_types=1);

namespace App\Middlewares\Auth;

use App\Exceptions\Common\UnauthorizedException;
use App\Globals\Auth\CurrentUser;
use App\Utilities\Http\Request;

/** Requires a signed-in user. The error handler turns the exception into 401 JSON or a sign-in redirect. */
final class Authenticate
{
    public function handle(Request $request, array $params): void
    {
        if (CurrentUser::get() === null) {
            throw new UnauthorizedException();
        }
    }
}
