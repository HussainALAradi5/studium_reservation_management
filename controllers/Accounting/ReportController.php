<?php

declare(strict_types=1);

namespace App\Controllers\Accounting;

use App\Controllers\Base\Controller;
use App\Dtos\Accounting\ReportQuery;
use App\Enums\Accounting\ExpenseCategory;
use App\Enums\Reservations\ReservationStatus;
use App\Services\Accounting\ReportService;
use App\Utilities\Http\Request;

final class ReportController extends Controller
{
    public function index(Request $request): void
    {
        $this->render($request, 'accounting/report', [
            'title' => 'Accounting report',
            'report' => (new ReportService())->buildSummary($this->user(), ReportQuery::fromQuery($request->queryAll())),
            'statuses' => ReservationStatus::options(),
            'categories' => ExpenseCategory::options(),
        ]);
    }
}
