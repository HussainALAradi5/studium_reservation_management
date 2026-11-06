<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Dtos\Accounting\ExpenseFormDto;
use App\Dtos\Accounting\ExpenseQuery;
use App\Dtos\Common\PageResult;
use App\Exceptions\Common\NotFoundException;
use App\Models\Users\User;
use App\Repositories\Accounting\ExpenseRepository;
use App\Services\Common\AccessPolicy;
use App\Services\Stadiums\StadiumService;

final class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepository $expenses = new ExpenseRepository(),
        private readonly StadiumService $stadiums = new StadiumService()
    ) {
    }

    public function list(User $user, ExpenseQuery $query): PageResult
    {
        return $this->expenses->paginate($query, AccessPolicy::scopeFor($user));
    }

    public function create(User $actor, ExpenseFormDto $dto): void
    {
        $stadium = $this->stadiums->findManageable($actor, $dto->stadiumCode);
        $this->expenses->insert([
            'stadium_id' => $stadium->id,
            'category' => $dto->category->value,
            'amount' => $dto->amount,
            'expense_date' => $dto->expenseDate,
            'description' => $dto->description,
        ]);
    }

    public function delete(User $actor, string $code): void
    {
        $expense = $this->expenses->findByCode($code);
        if ($expense === null || !AccessPolicy::canManageStadium($actor, $expense->stadiumManagerId)) {
            throw new NotFoundException('Expense not found.');
        }
        $this->expenses->softDelete($expense->id);
    }
}
