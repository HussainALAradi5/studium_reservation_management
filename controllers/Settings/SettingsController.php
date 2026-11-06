<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Controllers\Base\Controller;
use App\Dtos\Settings\SettingsFormDto;
use App\Dtos\Users\RolePermissionsDto;
use App\Enums\Settings\SettingsKey;
use App\Enums\Users\Permission;
use App\Enums\Users\UserRole;
use App\Services\Settings\SettingsService;
use App\Services\Users\PermissionService;
use App\Utilities\Http\Request;

/** Booking rules (settings keys) and the role × permission matrix. */
final class SettingsController extends Controller
{
    public function index(Request $request): void
    {
        $this->render($request, 'settings/index', [
            'title' => 'Settings',
            'keys' => SettingsKey::cases(),
            'values' => (new SettingsService())->all(),
            'permissions' => Permission::cases(),
            'roles' => UserRole::cases(),
            'matrix' => (new PermissionService())->matrix(),
        ]);
    }

    public function update(Request $request): void
    {
        (new SettingsService())->update(SettingsFormDto::fromInput($request->all()));
        $this->done('Settings saved.');
    }

    public function updatePermissions(Request $request): void
    {
        $dto = RolePermissionsDto::fromInput($request->param('role'), $request->all());
        (new PermissionService())->updateRole($dto->role, $dto->permissions);
        $this->done("Permissions for {$dto->role->label()} saved.");
    }
}
