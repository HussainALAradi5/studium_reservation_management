<?php

declare(strict_types=1);

namespace App\Controllers\Accounting;

use App\Controllers\Base\Controller;
use App\Dtos\Accounting\ExpenseFormDto;
use App\Dtos\Accounting\ExpenseQuery;
use App\Enums\Accounting\ExpenseCategory;
use App\Services\Accounting\ExpenseService;
use App\Services\Stadiums\StadiumService;
use App\Utilities\Http\Request;

final class ExpenseController extends Controller
{
    public function index(Request $request): void
    {
        $user = $this->user();
        $this->render($request, 'accounting/expenses', [
            'title' => 'Expenses',
            'page' => (new ExpenseService())->list($user, ExpenseQuery::fromQuery($request->queryAll())),
            'filters' => $request->queryAll(),
            'stadiums' => (new StadiumService())->listForSelect($user),
            'categories' => ExpenseCategory::options(),
        ]);
    }

    public function store(Request $request): void
    {
        (new ExpenseService())->create($this->user(), ExpenseFormDto::fromInput($request->all()));
        $this->done('Expense added.', null, 201);
    }

    public function destroy(Request $request): void
    {
        (new ExpenseService())->delete($this->user(), $request->param('code'));
        $this->done('Expense deleted.');
    }
}
