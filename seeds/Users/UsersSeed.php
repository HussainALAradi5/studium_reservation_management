<?php

declare(strict_types=1);

namespace App\Seeds\Users;

use App\Enums\Users\UserRole;
use App\Repositories\Users\UserRepository;
use App\Utilities\Security\PasswordHasher;

/** One development user per role. Existing users with the same username are left untouched. */
final class UsersSeed
{
    private const USERS = [
        ['admin', UserRole::ADMIN, 'System Admin', 'admin@studium.test', '+97330000001'],
        ['manager', UserRole::MANAGER, 'Field Manager', 'manager@studium.test', '+97330000002'],
        ['customer', UserRole::CUSTOMER, 'Ali Customer', 'customer@studium.test', '+97330000003'],
    ];

    /** @return array<string, int> username => id */
    public function run(string $password): array
    {
        $users = new UserRepository();
        $ids = [];
        foreach (self::USERS as [$username, $role, $name, $email, $phone]) {
            $existing = $users->findByLogin($username);
            $ids[$username] = $existing?->id ?? $users->insert([
                'role' => $role->value,
                'full_name' => $name,
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => PasswordHasher::hash($password),
                'is_active' => 1,
            ]);
        }
        echo "Seeded users: " . implode(', ', array_keys($ids)) . ".\n";
        return $ids;
    }
}
