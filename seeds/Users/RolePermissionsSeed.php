<?php

declare(strict_types=1);

namespace App\Seeds\Users;

use App\Services\Users\PermissionService;

final class RolePermissionsSeed
{
    public function run(): void
    {
        (new PermissionService())->seedDefaults();
        echo "Seeded default role permissions.\n";
    }
}
