<?php

declare(strict_types=1);

namespace App\Controllers\Home;

use App\Controllers\Base\Controller;
use App\Dtos\Accounting\ReportQuery;
use App\Dtos\Stadiums\StadiumQuery;
use App\Enums\Stadiums\SportType;
use App\Services\Accounting\ReportService;
use App\Services\Regional\CityService;
use App\Services\Reservations\ReservationService;
use App\Services\Stadiums\StadiumService;
use App\Services\Users\UserService;
use App\Utilities\Http\Request;

final class HomeController extends Controller
{
    private const FEATURED_COUNT = 6;
    private const UPCOMING_COUNT = 6;

    public function landing(Request $request): void
    {
        $featured = (new StadiumService())->browse(StadiumQuery::fromQuery([], self::FEATURED_COUNT));
        $this->render($request, 'home/landing', [
            'title' => 'Book a stadium',
            'stadiums' => $featured->items,
            'cities' => (new CityService())->listActive(),
            'sports' => SportType::options(),
        ]);
    }

    /** Role-aware dashboard: bookings for everyone, money for managers/admins, users for admins. */
    public function dashboard(Request $request): void
    {
        $user = $this->user();
        $reservations = new ReservationService();
        $data = [
            'title' => 'Dashboard',
            'upcoming' => $reservations->listUpcoming($user, self::UPCOMING_COUNT),
            'statusCounts' => $reservations->countByStatus($user),
            'summary' => null,
            'roleCounts' => [],
        ];
        if (!$user->isCustomer()) {
            $data['summary'] = (new ReportService())->buildSummary($user, ReportQuery::fromQuery([]));
        }
        if ($user->isAdmin()) {
            $data['roleCounts'] = (new UserService())->countByRole();
        }
        $this->render($request, 'home/dashboard', $data);
    }
}
