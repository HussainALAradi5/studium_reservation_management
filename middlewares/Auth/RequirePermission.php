<?php

declare(strict_types=1);

namespace App\Middlewares\Auth;

use App\Enums\Users\Permission;
use App\Exceptions\Common\ForbiddenException;
use App\Exceptions\Common\UnauthorizedException;
use App\Globals\Auth\CurrentUser;
use App\Services\Users\PermissionService;
use App\Utilities\Http\Request;

/** "can:reservations.manage" — the signed-in user's role must hold the permission (403 otherwise). */
final class RequirePermission
{
    public function handle(Request $request, array $params): void
    {
        $user = CurrentUser::get() ?? throw new UnauthorizedException();
        $service = new PermissionService();
        foreach ($params as $value) {
            if (!$service->can($user, Permission::from($value))) {
                throw new ForbiddenException();
            }
        }
    }
}
