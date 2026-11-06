<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Constants\Common\CodePrefixes;
use App\Repositories\Base\BaseRepository;

final class RolePermissionRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'role_permissions';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::ROLE_PERMISSION;
    }

    /** @return string[] permission values granted to the role */
    public function listForRole(string $role): array
    {
        $rows = $this->fetchAllRows(
            'SELECT permission FROM role_permissions WHERE role = :role AND deleted_at IS NULL',
            ['role' => $role]
        );
        return array_column($rows, 'permission');
    }

    /** Replaces a role's grants; call inside a transaction. */
    public function replaceForRole(string $role, array $permissions): void
    {
        $this->execute('DELETE FROM role_permissions WHERE role = :role', ['role' => $role]);
        foreach ($permissions as $permission) {
            $this->insert(['role' => $role, 'permission' => $permission]);
        }
    }
}
