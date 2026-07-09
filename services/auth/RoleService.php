<?php

namespace App\Services\auth;

use App\Models\Auth\Role as AuthRole;
use App\Services\BaseService;

class RoleService extends BaseService
{
    public function createRole(AuthRole $role, array $permissionIds): bool
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Create the base role
            $stmt = $this->pdo->prepare("INSERT INTO groups (name, description) VALUES (?, ?)");
            $stmt->execute([$role->name, $role->description]);
            $roleId = $this->pdo->lastInsertId();

            // 2. Attach permissions to the role
            if (!empty($permissionIds)) {
                $this->attachPermissions($roleId, $permissionIds);
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function assignUserToRole(string $userCode, int $roleId): bool
    {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO user_groups (user_code, group_id) VALUES (?, ?)");
        return $stmt->execute([$userCode, $roleId]);
    }

    private function attachPermissions(int $roleId, array $permissionIds): void
    {
        $sql = "INSERT INTO group_permissions (group_id, permission_id) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($permissionIds as $permissionId) {
            $stmt->execute([$roleId, $permissionId]);
        }
    }
}