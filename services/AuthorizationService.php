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
    public function login(string $userName, string $password): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userName = ? AND isActive = 1");
        $stmt->execute([$userName]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row && Security::verifyPassword($password, $row['password'])) {
            return User::fromDatabase($row);
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
    /**
     * Odoo-style access control check.
     * @param User $user The logged-in User object
     */
    public function can(User $user, string $action, string $scope = 'own', ?int $resourceOwnerId = null): bool
    {
        // Now $user->userType is the UserType Enum
        $isAdmin = ($user->userType === UserType::ADMIN);

        // Now $user->code is the integer
        $isOwner = ($resourceOwnerId !== null && $user->code === $resourceOwnerId);

        if ($isAdmin) {
            return true;
        }

        if ($scope === 'all') {
            return false;
        }

        return Access::any($isOwner);
    }
}
