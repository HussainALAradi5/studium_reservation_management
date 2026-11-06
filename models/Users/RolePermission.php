<?php

declare(strict_types=1);

namespace App\Models\Users;

use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use App\Models\Base\BaseEntity;

/** One permission granted to one role. */
final class RolePermission extends BaseEntity
{
    public UserRole $role;
    public Permission $permission;

    public static function fromRow(array $row): static
    {
        $grant = new self();
        $grant->fillBase($row);
        $grant->role = UserRole::from($row['role']);
        $grant->permission = Permission::from($row['permission']);
        return $grant;
    }
}
