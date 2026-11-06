<?php

declare(strict_types=1);

namespace App\Config\App;

use App\Globals\Auth\CurrentUser;
use App\Middlewares\Errors\ErrorHandler;
use App\Middlewares\Security\SecurityHeaders;
use App\Middlewares\Security\VerifyCsrf;
use App\Services\Users\AuthService;
use App\Utilities\Http\Request;
use App\Utilities\Http\Router;
use App\Utilities\Security\Session;

/** Request pipeline: session → security headers → current user → CSRF → route → controller. */
final class Kernel
{
    public static function handle(): void
    {
        $request = new Request();
        try {
            Session::start();
            (new SecurityHeaders())->handle($request, []);
            CurrentUser::set((new AuthService())->resolveSessionUser());
            (new VerifyCsrf())->handle($request, []);

            $router = new Router(require BASE_DIR . '/config/Routes/middleware.php');
            (require BASE_DIR . '/config/Routes/routes.php')($router);
            $router->dispatch($request);
        } catch (\Throwable $error) {
            ErrorHandler::handle($error, $request);
        }
    }
}
