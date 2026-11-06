<?php

declare(strict_types=1);

namespace App\Controllers\Users;

use App\Controllers\Base\Controller;
use App\Dtos\Users\LoginDto;
use App\Dtos\Users\RegisterDto;
use App\Services\Users\AuthService;
use App\Utilities\Http\Request;
use App\Utilities\Security\Session;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->render($request, 'auth/login', ['title' => 'Sign in', 'next' => $this->safeNext($request->query('next'))], 'layouts/auth');
    }

    public function showRegister(Request $request): void
    {
        $this->render($request, 'auth/register', ['title' => 'Create account'], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $user = (new AuthService())->login(LoginDto::fromInput($request->all(), $request->ip()));
        $next = $this->safeNext($request->input('next'));
        Session::flash('success', 'Welcome back, ' . $user->fullName . '!');
        $this->done('Signed in.', $next);
    }

    public function register(Request $request): void
    {
        $user = (new AuthService())->register(RegisterDto::fromInput($request->all()));
        Session::flash('success', 'Welcome to Studium, ' . $user->fullName . '!');
        $this->done('Account created.', '/stadiums', 201);
    }

    public function logout(Request $request): void
    {
        (new AuthService())->logout();
        $this->done('Signed out.', '/login');
    }

    /** Only internal paths are allowed as redirect targets (no open redirects). */
    private function safeNext(mixed $next): string
    {
        $next = is_string($next) ? $next : '';
        return preg_match('#^/(?!/)[A-Za-z0-9/_\-]*$#', $next) === 1 && !str_starts_with($next, '/api/') ? $next : '/dashboard';
    }
}
