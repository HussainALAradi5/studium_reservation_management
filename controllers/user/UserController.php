<?php

namespace App\Controllers\user;

use App\Models\User\User;
use App\Services\User\UserService;
use App\Services\AuthorizationService;
use App\Utilities\Access;

class UserController
{
    public function __construct(
        private UserService $userService,
        private AuthorizationService $authService
    ) {}

    public function register(User $newUser): bool
    {
        return $this->authService->register(
            $newUser->userName,
            $newUser->password,
            $newUser->email,
            $newUser->phoneNumber,
            $newUser->gender->value,
            $newUser->userType->value
        );
    }

    public function create(User $newUser, User $currentUser): bool
    {
        if (!$this->authService->can($currentUser, Access::CREATE, 'all')) {
            throw new \Exception("Unauthorized: Only admins can create users.");
        }
        return $this->userService->createUser($newUser);
    }


    public function update(int $userCode, User $updatedUser, User $currentUser): bool
    {
        $isAuthorized = $this->authService->can($currentUser, Access::WRITE, 'own', $userCode) ||
            $this->authService->can($currentUser, Access::WRITE, 'all');

        if (!$isAuthorized) {
            throw new \Exception("Unauthorized: Access denied.");
        }
        return $this->userService->updateUser($userCode, $updatedUser);
    }


    public function delete(int $userCode, User $currentUser): bool
    {
        $isAuthorized = $this->authService->can($currentUser, Access::DELETE, 'own', $userCode) ||
            $this->authService->can($currentUser, Access::DELETE, 'all');

        if (!$isAuthorized) {
            throw new \Exception("Unauthorized: You do not have permission to delete this account.");
        }

        return $this->userService->deleteUser($userCode);
    }
}
