<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\ValidationException;

final class RolePermissionsDto
{
    /** @param Permission[] $permissions */
    public function __construct(
        public readonly UserRole $role,
        public readonly array $permissions
    ) {
    }

    public static function fromInput(string $role, array $input): self
    {
        $userRole = UserRole::tryFrom($role) ?? throw ValidationException::forField('role', 'Choose a valid role.');
        $raw = $input['permissions'] ?? [];
        if (!is_array($raw)) {
            throw ValidationException::forField('permissions', 'Permissions must be a list.');
        }
        $permissions = [];
        foreach ($raw as $value) {
            $permissions[] = Permission::tryFrom((string) $value)
                ?? throw ValidationException::forField('permissions', "Unknown permission: $value");
        }
        return new self($userRole, $permissions);
    }
}
