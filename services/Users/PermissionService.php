<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Config\Database\Database;
use App\Constants\Common\ErrorCodes;
use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\ConflictException;
use App\Models\Users\User;
use App\Repositories\Users\RolePermissionRepository;

/** Role → permission grants, cached per request. Admins always hold every permission. */
final class PermissionService
{
    /** @var array<string, string[]> */
    private static array $cache = [];

    public function __construct(private readonly RolePermissionRepository $grants = new RolePermissionRepository())
    {
    }

    public function can(User $user, Permission $permission): bool
    {
        return in_array($permission->value, $this->granted($user->role), true);
    }

    /** @return string[] */
    public function granted(UserRole $role): array
    {
        if ($role === UserRole::ADMIN) {
            return Permission::values();
        }
        return self::$cache[$role->value] ??= $this->grants->listForRole($role->value);
    }

    /** @return array<string, string[]> role => granted permission values */
    public function matrix(): array
    {
        $matrix = [];
        foreach (UserRole::cases() as $role) {
            $matrix[$role->value] = $this->granted($role);
        }
        return $matrix;
    }

    /** @param Permission[] $permissions */
    public function updateRole(UserRole $role, array $permissions): void
    {
        if ($role === UserRole::ADMIN) {
            throw new ConflictException('Admins always keep every permission.', ErrorCodes::SELF_ACTION_FORBIDDEN);
        }
        $values = array_values(array_unique(array_map(static fn (Permission $p) => $p->value, $permissions)));
        Database::transaction(fn () => $this->grants->replaceForRole($role->value, $values));
        unset(self::$cache[$role->value]);
    }

    /** Writes the default grants for every role (used by the seed). */
    public function seedDefaults(): void
    {
        Database::transaction(function () {
            foreach (UserRole::cases() as $role) {
                $values = array_map(static fn (Permission $p) => $p->value, Permission::defaultsFor($role));
                $this->grants->replaceForRole($role->value, $values);
            }
        });
        self::$cache = [];
    }
}
