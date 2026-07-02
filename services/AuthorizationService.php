<?php

namespace App\Services;

use App\Models\User\User;
use App\Utilities\Access;
use App\Utilities\Security;
use App\Enums\User\Gender;
use App\Enums\User\UserType;
use App\Services\User\UserService;

class AuthorizationService extends BaseService
{
    private UserService $userService;

    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo);
        $this->userService = new UserService($pdo);
    }

    /**
     * Reuses UserService logic to prevent code duplication.
     */
    public function register(string $userName, string $password, string $email, string $phoneNumber, string $gender, string $userType): bool
    {
        $user = User::fromRegistration(
            $userName,
            $password,
            $email,
            $phoneNumber,
            Gender::from($gender),
            UserType::from($userType)
        );

        return $this->userService->createUser($user);
    }

    /**
     * Authenticates the user.
     */
    public function login(string $userName, string $password): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userName = ? AND isActive = 1");
        $stmt->execute([$userName]);
        $user = $stmt->fetch();

        if ($user && Security::verifyPassword($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    /**
     * Odoo-style access control check.
     * @param array $user Logged-in user data
     * @param string $action ('read', 'write', 'delete', 'create')
     * @param string $scope ('own' or 'all')
     * @param int|null $resourceOwnerId ID of the object owner
     */
    public function can(array $user, string $action, string $scope = 'own', ?int $resourceOwnerId = null): bool
    {
        $isAdmin = ($user['userType'] === UserType::ADMIN->value);
        $isOwner = ($resourceOwnerId !== null && (int)$user['code'] === $resourceOwnerId);

        // 1. Admin can perform any action on anything ('all')
        if ($isAdmin) {
            return true;
        }

        // 2. If scope is 'all', only Admins (handled above) can proceed
        if ($scope === 'all') {
            return false;
        }

        // 3. For 'own' scope, allow if user is the owner
        // You can expand this with Access::all() if you need stricter conditions
        return Access::any($isOwner);
    }
}
