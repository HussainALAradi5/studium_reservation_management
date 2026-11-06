<?php

declare(strict_types=1);

use App\Middlewares\Auth\Authenticate;
use App\Middlewares\Auth\RedirectIfAuthenticated;
use App\Middlewares\Auth\RequirePermission;

/** Route middleware aliases. CSRF and security headers run globally in the Kernel. */
return [
    'auth' => Authenticate::class,
    'guest' => RedirectIfAuthenticated::class,
    'can' => RequirePermission::class,
];
