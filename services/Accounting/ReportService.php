<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Dtos\Accounting\ReportQuery;
use App\Models\Stadiums\Stadium;
use App\Models\Users\User;
use App\Repositories\Accounting\ReportRepository;
use App\Services\Stadiums\StadiumService;
use App\Utilities\Dates\OpeningWindow;
use App\Utilities\Formatting\Money;

/** Rental accounting summary for a period (docs: workflows/accounting.md#report). */
final class ReportService
{
    public function __construct(
        private readonly ReportRepository $reports = new ReportRepository(),
        private readonly StadiumService $stadiums = new StadiumService()
    ) {
    }

    public function buildSummary(User $user, ReportQuery $query): array
    {
        $stadiums = $this->resolveStadiums($user, $query->stadiumCode);
        $ids = array_map(static fn (Stadium $stadium) => $stadium->id, $stadiums);

        $cash = $this->reports->cashByStadium($ids, $query->from, $query->to);
        $expenses = $this->reports->expensesByStadium($ids, $query->from, $query->to);
        $bookings = $this->reports->bookingsByStadiumAndStatus($ids, $query->from, $query->to);

        $perStadium = [];
        $statusCounts = [];
        $totals = ['gross' => 0.0, 'refunds' => 0.0, 'expenses' => 0.0, 'bookedValue' => 0.0, 'outstanding' => 0.0, 'bookedMinutes' => 0, 'openMinutes' => 0];
        $billable = ReportRepository::billableStatuses();

        foreach ($stadiums as $stadium) {
            $perStadium[$stadium->id] = [
                'code' => $stadium->code,
                'name' => $stadium->name,
                'gross' => (float) ($cash[$stadium->id]['gross'] ?? 0),
                'refunds' => (float) ($cash[$stadium->id]['refunds'] ?? 0),
                'expenses' => (float) ($expenses[$stadium->id]['total'] ?? 0),
                'bookings' => 0,
            ];
            $totals['openMinutes'] += $this->openMinutes($stadium, $query->from, $query->to);
        }

        foreach ($bookings as $row) {
            $statusCounts[$row['status']] = ($statusCounts[$row['status']] ?? 0) + (int) $row['bookings'];
            $perStadium[(int) $row['stadium_id']]['bookings'] += (int) $row['bookings'];
            if (in_array($row['status'], $billable, true)) {
                $totals['bookedValue'] += (float) $row['booked_value'];
                $totals['outstanding'] += (float) $row['outstanding'];
                $totals['bookedMinutes'] += (int) $row['minutes'];
            }
        }

        foreach ($perStadium as &$row) {
            $row['net'] = Money::round($row['gross'] - $row['refunds']);
            $row['profit'] = Money::round($row['net'] - $row['expenses']);
            $totals['gross'] += $row['gross'];
            $totals['refunds'] += $row['refunds'];
            $totals['expenses'] += $row['expenses'];
        }
        unset($row);

        $net = Money::round($totals['gross'] - $totals['refunds']);
        return [
            'from' => $query->from,
            'to' => $query->to,
            'stadiums' => $stadiums,
            'selectedStadiumCode' => $query->stadiumCode,
            'grossRevenue' => Money::round($totals['gross']),
            'refunds' => Money::round($totals['refunds']),
            'netRevenue' => $net,
            'expenses' => Money::round($totals['expenses']),
            'netProfit' => Money::round($net - $totals['expenses']),
            'bookedValue' => Money::round($totals['bookedValue']),
            'outstanding' => Money::round($totals['outstanding']),
            'utilization' => $totals['openMinutes'] > 0 ? round($totals['bookedMinutes'] / $totals['openMinutes'] * 100, 1) : 0.0,
            'bookedHours' => round($totals['bookedMinutes'] / 60, 1),
            'statusCounts' => $statusCounts,
            'expensesByCategory' => $this->reports->expensesByCategory($ids, $query->from, $query->to),
            'daily' => $this->fillDays($this->reports->dailyNetCash($ids, $query->from, $query->to), $query->from, $query->to),
            'perStadium' => array_values($perStadium),
        ];
    }

    /** @return Stadium[] stadiums in scope, narrowed to one when a code is given */
    private function resolveStadiums(User $user, string $stadiumCode): array
    {
        if ($stadiumCode !== '') {
            return [$this->stadiums->findManageable($user, $stadiumCode)];
        }
        return $this->stadiums->listForSelect($user);
    }

    private function openMinutes(Stadium $stadium, string $from, string $to): int
    {
        $days = (new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days + 1;
        $window = OpeningWindow::forDate(new \DateTimeImmutable($from), $stadium->openingTime, $stadium->closingTime);
        return $days * $window->minutes();
    }

    /** Every day of the period with its net cash (0 when nothing moved). */
    private function fillDays(array $daily, string $from, string $to): array
    {
        $result = [];
        $end = new \DateTimeImmutable($to);
        for ($day = new \DateTimeImmutable($from); $day <= $end; $day = $day->modify('+1 day')) {
            $key = $day->format('Y-m-d');
            $result[$key] = Money::round($daily[$key] ?? 0.0);
        }
        return $result;
    }
}
