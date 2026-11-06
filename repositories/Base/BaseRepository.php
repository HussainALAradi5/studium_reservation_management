<?php

declare(strict_types=1);

namespace App\Repositories\Base;

use App\Config\Database\Database;
use App\Globals\Auth\CurrentUser;
use App\Utilities\Codes\CodeGenerator;
use PDO;

/**
 * Shared persistence: generates codes, fills audit fields from the signed-in user,
 * soft-deletes, and runs prepared statements. Concrete repositories add their queries.
 */
abstract class BaseRepository
{
    private const MAX_CODE_ATTEMPTS = 5;

    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    abstract protected function table(): string;

    abstract protected function codePrefix(): string;

    /** Inserts a row with a fresh unique code and audit fields; returns the new id. */
    public function insert(array $data): int
    {
        $now = self::now();
        $data += [
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => CurrentUser::id(),
            'updated_by' => CurrentUser::id(),
        ];
        for ($attempt = 1; ; $attempt++) {
            $data['code'] = CodeGenerator::generate($this->codePrefix());
            try {
                $this->execute($this->buildInsert($data), $data);
                return (int) $this->pdo->lastInsertId();
            } catch (\PDOException $e) {
                $isCodeCollision = $e->getCode() === '23000' && str_contains($e->getMessage(), '_code');
                if (!$isCodeCollision || $attempt >= self::MAX_CODE_ATTEMPTS) {
                    throw $e;
                }
            }
        }
    }

    public function update(int $id, array $data): void
    {
        $data['updated_at'] = self::now();
        $data['updated_by'] = CurrentUser::id();
        $assignments = implode(', ', array_map(static fn ($column) => "$column = :$column", array_keys($data)));
        $this->execute("UPDATE {$this->table()} SET $assignments WHERE id = :id", [...$data, 'id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->execute(
            "UPDATE {$this->table()} SET deleted_at = :deleted_at, deleted_by = :deleted_by WHERE id = :id",
            ['deleted_at' => self::now(), 'deleted_by' => CurrentUser::id(), 'id' => $id]
        );
    }

    /** Duplicate check among non-deleted rows. $column must come from code, never from input. */
    public function existsWhere(string $column, mixed $value, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table()} WHERE $column = :value AND deleted_at IS NULL";
        $params = ['value' => $value];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        return $this->fetchOne($sql . ' LIMIT 1', $params) !== null;
    }

    protected function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchAllRows(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    protected function fetchValue(string $sql, array $params = []): mixed
    {
        return $this->execute($sql, $params)->fetchColumn();
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function buildInsert(array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn ($column) => ":$column", $columns);
        return sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table(), implode(', ', $columns), implode(', ', $placeholders));
    }
}
