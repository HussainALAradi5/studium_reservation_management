<?php

declare(strict_types=1);

namespace App\Repositories\Accounting;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Accounting\ExpenseQuery;
use App\Dtos\Common\AccessScope;
use App\Dtos\Common\PageResult;
use App\Models\Accounting\Expense;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class ExpenseRepository extends BaseRepository
{
    private const SELECT = 'e.*, s.name AS stadium_name, s.manager_id AS stadium_manager_id';
    private const FROM = 'FROM expenses e JOIN stadiums s ON s.id = e.stadium_id';
    private const SORT_COLUMNS = ['date' => 'e.expense_date', 'amount' => 'e.amount', 'created' => 'e.created_at'];

    protected function table(): string
    {
        return 'expenses';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::EXPENSE;
    }

    public function findByCode(string $code): ?Expense
    {
        $row = $this->fetchOne(
            'SELECT ' . self::SELECT . ' ' . self::FROM . ' WHERE e.code = :code AND e.deleted_at IS NULL',
            ['code' => IdentityNormalizer::code($code)]
        );
        return $row ? Expense::fromRow($row) : null;
    }

    public function paginate(ExpenseQuery $query, AccessScope $scope): PageResult
    {
        [$scopeSql, $params] = $scope->toSql('s', null);
        $where = ['e.deleted_at IS NULL', $scopeSql];
        if ($query->stadiumCode !== '') {
            $where[] = 's.code = :stadium_code';
            $params['stadium_code'] = $query->stadiumCode;
        }
        if ($query->category !== null) {
            $where[] = 'e.category = :category';
            $params['category'] = $query->category->value;
        }
        if ($query->from !== null) {
            $where[] = 'e.expense_date >= :from_date';
            $params['from_date'] = $query->from;
        }
        if ($query->to !== null) {
            $where[] = 'e.expense_date <= :to_date';
            $params['to_date'] = $query->to;
        }
        return Paginator::paginate(
            $this->pdo,
            self::SELECT,
            self::FROM . ' WHERE ' . implode(' AND ', $where),
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => Expense::fromRow($row)
        );
    }
}
