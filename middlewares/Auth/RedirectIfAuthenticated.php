<?php

declare(strict_types=1);

namespace App\Middlewares\Auth;

use App\Globals\Auth\CurrentUser;
use App\Utilities\Http\Redirect;
use App\Utilities\Http\Request;

/** Sign-in and register pages send signed-in users to their dashboard. */
final class RedirectIfAuthenticated
{
    public function handle(Request $request, array $params): void
    {
        if (CurrentUser::get() !== null) {
            Redirect::to('/dashboard');
        }
    }
}
