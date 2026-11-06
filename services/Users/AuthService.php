<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Config\App\AppConfig;
use App\Constants\Users\AuthRules;
use App\Dtos\Users\LoginDto;
use App\Dtos\Users\RegisterDto;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\TooManyRequestsException;
use App\Exceptions\Common\UnauthorizedException;
use App\Models\Users\User;
use App\Repositories\Users\LoginAttemptRepository;
use App\Repositories\Users\UserRepository;
use App\Utilities\Security\PasswordHasher;
use App\Utilities\Security\Session;

final class AuthService
{
    private const INVALID_CREDENTIALS = 'Incorrect username or password.';

    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly LoginAttemptRepository $attempts = new LoginAttemptRepository(),
        private readonly UserService $userService = new UserService()
    ) {
    }

    /** Creates a customer account and signs it in. */
    public function register(RegisterDto $dto): User
    {
        $this->userService->assertUnique($dto->username, $dto->email, $dto->phone);
        $id = $this->users->insert([
            'role' => UserRole::CUSTOMER->value,
            'full_name' => $dto->fullName,
            'username' => $dto->username,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'password_hash' => PasswordHasher::hash($dto->password),
            'is_active' => 1,
        ]);
        $user = $this->users->findById($id);
        $this->startSession($user);
        return $user;
    }

    /** Verifies credentials with throttling and starts a fresh session. */
    public function login(LoginDto $dto): User
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . AuthRules::LOGIN_WINDOW_MINUTES . ' minutes'));
        if ($this->attempts->countSince($dto->identifier, $dto->ip, $since) >= AuthRules::MAX_LOGIN_ATTEMPTS) {
            throw new TooManyRequestsException(
                'Too many failed attempts. Try again in ' . AuthRules::LOGIN_WINDOW_MINUTES . ' minutes.'
            );
        }

        $user = $this->users->findByLogin($dto->identifier);
        if ($user === null || !$user->isActive || !PasswordHasher::verify($dto->password, $user->passwordHash)) {
            $this->attempts->record($dto->identifier, $dto->ip);
            throw new UnauthorizedException(self::INVALID_CREDENTIALS);
        }

        $this->attempts->clear($dto->identifier, $dto->ip);
        $this->users->touchLastLogin($user->id);
        $this->startSession($user);
        return $user;
    }

    public function logout(): void
    {
        Session::destroy();
    }

    /** Loads the signed-in user from the session; null when signed out, idle too long, or deactivated. */
    public function resolveSessionUser(): ?User
    {
        $userId = Session::get(AuthRules::SESSION_USER_KEY);
        if (!is_int($userId)) {
            return null;
        }
        $lastActivity = (int) Session::get(AuthRules::SESSION_ACTIVITY_KEY, 0);
        if (time() - $lastActivity > AppConfig::sessionIdleMinutes() * 60) {
            Session::forget(AuthRules::SESSION_USER_KEY);
            return null;
        }
        $user = $this->users->findById($userId);
        if ($user === null || !$user->isActive) {
            Session::forget(AuthRules::SESSION_USER_KEY);
            return null;
        }
        Session::set(AuthRules::SESSION_ACTIVITY_KEY, time());
        return $user;
    }

    private function startSession(User $user): void
    {
        Session::regenerate();
        Session::set(AuthRules::SESSION_USER_KEY, $user->id);
        Session::set(AuthRules::SESSION_ACTIVITY_KEY, time());
    }
}
