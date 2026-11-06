<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Common\PageResult;
use App\Dtos\Users\UserQuery;
use App\Enums\Users\UserRole;
use App\Models\Users\User;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class UserRepository extends BaseRepository
{
    private const SORT_COLUMNS = ['created' => 'u.created_at', 'name' => 'u.full_name', 'role' => 'u.role'];

    protected function table(): string
    {
        return 'users';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::USER;
    }

    public function findById(int $id): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
        return $row ? User::fromRow($row) : null;
    }

    public function findByCode(string $code): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE code = :code AND deleted_at IS NULL', ['code' => IdentityNormalizer::code($code)]);
        return $row ? User::fromRow($row) : null;
    }

    /** Finds a user by username or email (sign-in). */
    public function findByLogin(string $identifier): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE (username = :username OR email = :email) AND deleted_at IS NULL LIMIT 1',
            ['username' => IdentityNormalizer::username($identifier), 'email' => IdentityNormalizer::email($identifier)]
        );
        return $row ? User::fromRow($row) : null;
    }

    public function paginate(UserQuery $query): PageResult
    {
        $where = ['u.deleted_at IS NULL'];
        $params = [];
        if ($query->search !== '') {
            $where[] = '(u.full_name LIKE :q1 OR u.username LIKE :q2 OR u.email LIKE :q3 OR u.phone LIKE :q4)';
            $like = '%' . $query->search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
        }
        if ($query->role !== null) {
            $where[] = 'u.role = :role';
            $params['role'] = $query->role->value;
        }
        if ($query->isActive !== null) {
            $where[] = 'u.is_active = :is_active';
            $params['is_active'] = (int) $query->isActive;
        }
        return Paginator::paginate(
            $this->pdo,
            'u.*',
            'FROM users u WHERE ' . implode(' AND ', $where),
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => User::fromRow($row)
        );
    }

    /** @return User[] active managers for stadium assignment */
    public function listActiveManagers(): array
    {
        $rows = $this->fetchAllRows(
            'SELECT * FROM users WHERE role = :role AND is_active = 1 AND deleted_at IS NULL ORDER BY full_name',
            ['role' => UserRole::MANAGER->value]
        );
        return array_map(static fn (array $row) => User::fromRow($row), $rows);
    }

    /** @return int[] ids of active admins (fallback recipients when a stadium has no manager) */
    public function listActiveAdminIds(): array
    {
        $rows = $this->fetchAllRows(
            'SELECT id FROM users WHERE role = :role AND is_active = 1 AND deleted_at IS NULL',
            ['role' => UserRole::ADMIN->value]
        );
        return array_map('intval', array_column($rows, 'id'));
    }

    public function countManagedStadiums(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM stadiums WHERE manager_id = :id AND deleted_at IS NULL',
            ['id' => $userId]
        );
    }

    public function touchLastLogin(int $userId): void
    {
        $this->execute('UPDATE users SET last_login_at = :now WHERE id = :id', ['now' => self::now(), 'id' => $userId]);
    }

    /** @return array<string, int> role => count of active users */
    public function countByRole(): array
    {
        $rows = $this->fetchAllRows(
            'SELECT role, COUNT(*) AS total FROM users WHERE deleted_at IS NULL AND is_active = 1 GROUP BY role'
        );
        return array_column($rows, 'total', 'role');
    }
}
