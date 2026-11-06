<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Constants\Common\ErrorCodes;
use App\Dtos\Common\PageResult;
use App\Dtos\Users\ChangePasswordDto;
use App\Dtos\Users\ProfileDto;
use App\Dtos\Users\UserFormDto;
use App\Dtos\Users\UserQuery;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\NotFoundException;
use App\Exceptions\Common\ValidationException;
use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use App\Utilities\Security\PasswordHasher;

final class UserService
{
    public function __construct(private readonly UserRepository $users = new UserRepository())
    {
    }

    public function list(UserQuery $query): PageResult
    {
        return $this->users->paginate($query);
    }

    public function findByCode(string $code): User
    {
        return $this->users->findByCode($code) ?? throw new NotFoundException('User not found.');
    }

    /** @return User[] */
    public function listManagers(): array
    {
        return $this->users->listActiveManagers();
    }

    public function create(UserFormDto $dto): User
    {
        $this->assertUnique($dto->username, $dto->email, $dto->phone);
        $id = $this->users->insert([
            ...$this->profileColumns($dto),
            'username' => $dto->username,
            'role' => $dto->role->value,
            'is_active' => (int) $dto->isActive,
            'password_hash' => PasswordHasher::hash((string) $dto->password),
        ]);
        return $this->users->findById($id);
    }

    public function update(User $actor, string $code, UserFormDto $dto): User
    {
        $user = $this->findByCode($code);
        $this->assertUnique($dto->username, $dto->email, $dto->phone, $user->id);
        $this->assertRoleChangeAllowed($actor, $user, $dto->role, $dto->isActive);

        $columns = [
            ...$this->profileColumns($dto),
            'username' => $dto->username,
            'role' => $dto->role->value,
            'is_active' => (int) $dto->isActive,
        ];
        if ($dto->password !== null) {
            $columns['password_hash'] = PasswordHasher::hash($dto->password);
        }
        $this->users->update($user->id, $columns);
        return $this->users->findById($user->id);
    }

    public function toggleActive(User $actor, string $code): User
    {
        $user = $this->findByCode($code);
        $this->assertRoleChangeAllowed($actor, $user, $user->role, !$user->isActive);
        $this->users->update($user->id, ['is_active' => (int) !$user->isActive]);
        return $this->users->findById($user->id);
    }

    public function updateProfile(User $user, ProfileDto $dto): User
    {
        $this->assertUnique($user->username, $dto->email, $dto->phone, $user->id);
        $this->users->update($user->id, $this->profileColumns($dto));
        return $this->users->findById($user->id);
    }

    public function changePassword(User $user, ChangePasswordDto $dto): void
    {
        if (!PasswordHasher::verify($dto->currentPassword, $user->passwordHash)) {
            throw ValidationException::forField('currentPassword', 'Current password is incorrect.');
        }
        $this->users->update($user->id, ['password_hash' => PasswordHasher::hash($dto->newPassword)]);
    }

    /** Throws 409 with a field error when username, email, or phone already belongs to another user. */
    public function assertUnique(string $username, string $email, string $phone, ?int $excludeId = null): void
    {
        $checks = ['username' => [$username, 'Username'], 'email' => [$email, 'Email'], 'phone' => [$phone, 'Phone']];
        foreach ($checks as $column => [$value, $label]) {
            if ($this->users->existsWhere($column, $value, $excludeId)) {
                throw ConflictException::duplicate($column, $label);
            }
        }
    }

    public function countByRole(): array
    {
        return $this->users->countByRole();
    }

    /** Admins cannot lock themselves out; managers with stadiums keep their role until reassigned. */
    private function assertRoleChangeAllowed(User $actor, User $target, UserRole $newRole, bool $newIsActive): void
    {
        $isSelf = $actor->id === $target->id;
        if ($isSelf && ($newRole !== $target->role || !$newIsActive)) {
            throw new ConflictException('You cannot change your own role or deactivate yourself.', ErrorCodes::SELF_ACTION_FORBIDDEN);
        }
        $isLeavingManagerRole = $target->isManager() && ($newRole !== UserRole::MANAGER || !$newIsActive);
        if ($isLeavingManagerRole && $this->users->countManagedStadiums($target->id) > 0) {
            throw new ConflictException(
                'Reassign this manager\'s stadiums before changing the role or deactivating.',
                ErrorCodes::MANAGER_HAS_STADIUMS
            );
        }
    }

    private function profileColumns(UserFormDto|ProfileDto $dto): array
    {
        return [
            'full_name' => $dto->fullName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'gender' => $dto->gender?->value,
            'birth_date' => $dto->birthDate,
        ];
    }
}
