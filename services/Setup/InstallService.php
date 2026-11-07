<?php

declare(strict_types=1);

namespace App\Services\Setup;

use App\Constants\Common\ErrorCodes;
use App\Dtos\Users\UserFormDto;
use App\Enums\Settings\SettingsKey;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\ConflictException;
use App\Models\Users\User;
use App\Repositories\Settings\SettingRepository;
use App\Repositories\Users\UserRepository;
use App\Services\Users\PermissionService;
use App\Services\Users\UserService;

/**
 * First-time production setup, safe to run again: default permissions for roles that have none,
 * missing settings with their defaults, and the first admin (only while no admin exists).
 */
final class InstallService
{
    public function __construct(
        private readonly PermissionService $permissions = new PermissionService(),
        private readonly SettingRepository $settings = new SettingRepository(),
        private readonly UserRepository $users = new UserRepository(),
        private readonly UserService $userService = new UserService()
    ) {
    }

    /** @return string[] roles that received default permissions */
    public function grantDefaultPermissions(): array
    {
        return $this->permissions->seedDefaultsForEmptyRoles();
    }

    /** @return string[] setting keys that were added */
    public function addMissingSettings(): array
    {
        $stored = $this->settings->allValues();
        $added = [];
        foreach (SettingsKey::cases() as $key) {
            if (!array_key_exists($key->value, $stored)) {
                $this->settings->upsert($key->value, (string) $key->defaultValue());
                $added[] = $key->value;
            }
        }
        return $added;
    }

    public function hasAdmin(): bool
    {
        return ($this->users->countByRole()[UserRole::ADMIN->value] ?? 0) > 0;
    }

    /** Creates the first admin; refuses when an active admin already exists. */
    public function createFirstAdmin(UserFormDto $dto): User
    {
        if ($this->hasAdmin()) {
            throw new ConflictException('An admin already exists. Sign in and create other users from the Users page.', ErrorCodes::CONFLICT);
        }
        return $this->userService->create($dto);
    }
}
