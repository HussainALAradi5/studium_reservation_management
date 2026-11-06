<?php

declare(strict_types=1);

namespace App\Controllers\Base;

use App\Exceptions\Common\UnauthorizedException;
use App\Globals\Auth\CurrentUser;
use App\Models\Users\User;
use App\Utilities\Http\Request;
use App\Utilities\Responses\ApiResponse;
use App\Utilities\Views\View;

/** Controllers translate HTTP to DTOs, call one service function, and render or return JSON. */
abstract class Controller
{
    protected function render(Request $request, string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        View::render($view, [...$data, 'currentPath' => $request->path()], $layout);
    }

    protected function json(mixed $data = null, int $status = 200, array $meta = []): void
    {
        ApiResponse::success($data, $status, $meta);
    }

    /** Standard result of an AJAX action: a toast message plus an optional redirect. */
    protected function done(string $message, ?string $redirect = null, int $status = 200, array $extra = []): void
    {
        $this->json(['message' => $message, 'redirect' => $redirect ? url($redirect) : null, ...$extra], $status);
    }

    protected function user(): User
    {
        return CurrentUser::get() ?? throw new UnauthorizedException();
    }
}
