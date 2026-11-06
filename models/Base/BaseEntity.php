<?php

declare(strict_types=1);

namespace App\Models\Base;

/** Fields shared by every persisted model (docs: server/models/Base/base-entity.md). */
abstract class BaseEntity
{
    public int $id = 0;
    public string $code = '';
    public string $createdAt = '';
    public string $updatedAt = '';
    public ?int $createdBy = null;
    public ?int $updatedBy = null;
    public ?string $deletedAt = null;
    public ?int $deletedBy = null;

    /** Builds the model from a database row. */
    abstract public static function fromRow(array $row): static;

    protected function fillBase(array $row): void
    {
        $this->id = (int) $row['id'];
        $this->code = (string) $row['code'];
        $this->createdAt = (string) ($row['created_at'] ?? '');
        $this->updatedAt = (string) ($row['updated_at'] ?? '');
        $this->createdBy = self::nullableInt($row['created_by'] ?? null);
        $this->updatedBy = self::nullableInt($row['updated_by'] ?? null);
        $this->deletedAt = $row['deleted_at'] ?? null;
        $this->deletedBy = self::nullableInt($row['deleted_by'] ?? null);
    }

    protected static function nullableInt(mixed $value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    protected static function nullableFloat(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
