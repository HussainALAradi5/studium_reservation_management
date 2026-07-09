<?php

namespace App\Controllers\Auth;

use App\Models\Auth\Role;
use App\Services\Auth\RoleService;
use App\Services\AuthorizationService;
use App\Models\User\User;

class RoleController
{
    public function __construct(
        private RoleService $roleService,
        private AuthorizationService $authService
    ) {}

    /**
     * Endpoint to create a new Role (e.g., 'HR Manager') and assign its permissions.
     */
    public function create(string $name, string $description, array $permissionIds, User $currentUser): bool
    {
        // 1. Gatekeeper Check: Does this user have permission to manage roles?
        if (!$this->authService->can($currentUser, 'roles', 'write', 'all')) {
            throw new \Exception("Unauthorized: Only super admins can create roles.");
        }

        // 2. Hydrate the model
        $newRole = new Role(null, $name, $description);

        // 3. Execute business logic
        return $this->roleService->createRole($newRole, $permissionIds);
    }

    /**
     * Endpoint to assign a user to a specific role.
     */
    public function assignRole(string $targetUserCode, int $roleId, User $currentUser): bool
    {
        // 1. Gatekeeper Check
        if (!$this->authService->can($currentUser, 'users', 'write', 'all')) {
            throw new \Exception("Unauthorized: Cannot assign roles.");
        }

        // 2. Execute business logic
        return $this->roleService->assignUserToRole($targetUserCode, $roleId);
    }
}
