<?php

declare(strict_types=1);

namespace App\Models\Accounting;

use App\Enums\Accounting\ExpenseCategory;
use App\Models\Base\BaseEntity;

final class Expense extends BaseEntity
{
    public int $stadiumId = 0;
    public string $stadiumName = '';
    public ?int $stadiumManagerId = null;
    public ExpenseCategory $category;
    public float $amount = 0.0;
    public string $expenseDate = '';
    public string $description = '';

    public static function fromRow(array $row): static
    {
        $expense = new self();
        $expense->fillBase($row);
        $expense->stadiumId = (int) $row['stadium_id'];
        $expense->stadiumName = (string) ($row['stadium_name'] ?? '');
        $expense->stadiumManagerId = self::nullableInt($row['stadium_manager_id'] ?? null);
        $expense->category = ExpenseCategory::from($row['category']);
        $expense->amount = (float) $row['amount'];
        $expense->expenseDate = $row['expense_date'];
        $expense->description = $row['description'];
        return $expense;
    }
}
