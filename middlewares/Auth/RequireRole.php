<?php

declare(strict_types=1);

namespace App\Middlewares\Auth;

use App\Enums\Users\UserRole;
use App\Exceptions\Common\ForbiddenException;
use App\Exceptions\Common\UnauthorizedException;
use App\Globals\Auth\CurrentUser;
use App\Utilities\Http\Request;

/** "role:admin,manager" — the signed-in user must have one of the listed roles. */
final class RequireRole
{
    public function handle(Request $request, array $params): void
    {
        $user = CurrentUser::get() ?? throw new UnauthorizedException();
        $roles = array_map(static fn (string $role) => UserRole::from($role), $params);
        if (!$user->hasRole(...$roles)) {
            throw new ForbiddenException();
        }
    }
}
