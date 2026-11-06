<?php

declare(strict_types=1);

namespace App\Models\Users;

use App\Enums\Users\Gender;
use App\Enums\Users\UserRole;
use App\Models\Base\BaseEntity;

final class User extends BaseEntity
{
    public UserRole $role;
    public string $fullName = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public string $passwordHash = '';
    public ?Gender $gender = null;
    public ?string $birthDate = null;
    public bool $isActive = true;
    public ?string $lastLoginAt = null;

    public static function fromRow(array $row): static
    {
        $user = new self();
        $user->fillBase($row);
        $user->role = UserRole::from($row['role']);
        $user->fullName = $row['full_name'];
        $user->username = $row['username'];
        $user->email = $row['email'];
        $user->phone = $row['phone'];
        $user->passwordHash = $row['password_hash'] ?? '';
        $user->gender = isset($row['gender']) ? Gender::from($row['gender']) : null;
        $user->birthDate = $row['birth_date'] ?? null;
        $user->isActive = (bool) $row['is_active'];
        $user->lastLoginAt = $row['last_login_at'] ?? null;
        return $user;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->fullName)) ?: [];
        $letters = array_map(static fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2));
        return mb_strtoupper(implode('', $letters));
    }
}
