<?php

declare(strict_types=1);

namespace App\Controllers\Stadiums;

use App\Config\App\AppConfig;
use App\Constants\Common\Pagination;
use App\Constants\Common\RegexPatterns;
use App\Constants\Reservations\ReservationRules;
use App\Constants\Stadiums\StadiumRules;
use App\Controllers\Base\Controller;
use App\Dtos\Reservations\BookingRequestDto;
use App\Dtos\Stadiums\StadiumFormDto;
use App\Dtos\Stadiums\StadiumQuery;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Settings\SettingsKey;
use App\Enums\Stadiums\SportType;
use App\Enums\Stadiums\SurfaceType;
use App\Exceptions\Common\ValidationException;
use App\Globals\Auth\CurrentUser;
use App\Models\Stadiums\Stadium;
use App\Services\Regional\CityService;
use App\Services\Reservations\ConflictService;
use App\Services\Settings\SettingsService;
use App\Services\Stadiums\StadiumService;
use App\Services\Users\UserService;
use App\Utilities\Http\Request;

final class StadiumController extends Controller
{
    private const BROWSE_PAGE_SIZE = 9;

    public function browse(Request $request): void
    {
        $this->render($request, 'stadiums/browse', [
            'title' => 'Stadiums',
            'page' => (new StadiumService())->browse(StadiumQuery::fromQuery($request->queryAll(), self::BROWSE_PAGE_SIZE)),
            'filters' => $request->queryAll(),
            'cities' => (new CityService())->listActive(),
            'sports' => SportType::options(),
        ]);
    }

    public function show(Request $request): void
    {
        $user = CurrentUser::get();
        $stadium = (new StadiumService())->findVisible($user, $request->param('code'));
        $this->render($request, 'stadiums/show', [
            'title' => $stadium->name,
            'stadium' => $stadium,
            'map' => AppConfig::map(),
            'needsMap' => true,
            'canBook' => $user === null || $user->isCustomer(),
            'bookingUnits' => BookingUnit::options(),
            'maxHours' => (new SettingsService())->int(SettingsKey::MAX_HOURS_PER_BOOKING),
            'maxDays' => (new SettingsService())->int(SettingsKey::MAX_DAYS_PER_BOOKING),
        ]);
    }

    /** GET /api/stadiums/{code}/availability?date=YYYY-MM-DD */
    public function availability(Request $request): void
    {
        $stadium = (new StadiumService())->findVisible(CurrentUser::get(), $request->param('code'));
        $date = (string) $request->query('date', date('Y-m-d'));
        if (preg_match(RegexPatterns::DATE_ISO, $date) !== 1 || strtotime($date) === false) {
            throw ValidationException::forField('date', 'Choose a valid date.');
        }
        $this->json([
            'date' => $date,
            'slotMinutes' => $stadium->slotMinutes,
            'pricePerHour' => $stadium->pricePerHour,
            'currency' => AppConfig::currency(),
            'hours' => $stadium->hoursLabel(),
            'slots' => (new ConflictService())->availability($stadium, new \DateTimeImmutable($date)),
        ]);
    }

    /** GET /api/stadiums/{code}/calendar?month=YYYY-MM — free / partial / full days for daily rentals. */
    public function calendar(Request $request): void
    {
        $stadium = (new StadiumService())->findVisible(CurrentUser::get(), $request->param('code'));
        $month = (string) $request->query('month', date('Y-m'));
        if (preg_match(ReservationRules::CALENDAR_MONTH_PATTERN, $month) !== 1) {
            throw ValidationException::forField('month', 'Choose a valid month.');
        }
        $this->json([
            'month' => $month,
            'pricePerDay' => $stadium->pricePerDay,
            'days' => (new ConflictService())->calendar($stadium, $month),
        ]);
    }

    /** GET /api/stadiums/{code}/quote — server-side price and availability for an hourly or daily request. */
    public function quote(Request $request): void
    {
        $stadium = (new StadiumService())->findVisible(CurrentUser::get(), $request->param('code'));
        $this->json((new ConflictService())->quote($stadium, BookingRequestDto::fromInput($request->queryAll())));
    }

    public function manage(Request $request): void
    {
        $this->render($request, 'stadiums/manage', [
            'title' => 'Manage stadiums',
            'page' => (new StadiumService())->listManaged($this->user(), StadiumQuery::fromQuery($request->queryAll(), Pagination::DEFAULT_PAGE_SIZE)),
            'filters' => $request->queryAll(),
            'cities' => (new CityService())->listActive(),
            'sports' => SportType::options(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->renderForm($request, null);
    }

    public function edit(Request $request): void
    {
        $this->renderForm($request, (new StadiumService())->findManageable($this->user(), $request->param('code')));
    }

    public function store(Request $request): void
    {
        $stadium = (new StadiumService())->create(StadiumFormDto::fromInput($request->all()));
        $this->done("Stadium \"{$stadium->name}\" created.", '/manage/stadiums', 201);
    }

    public function update(Request $request): void
    {
        $stadium = (new StadiumService())->update($this->user(), $request->param('code'), StadiumFormDto::fromInput($request->all()));
        $this->done("Stadium \"{$stadium->name}\" saved.", '/manage/stadiums');
    }

    public function toggle(Request $request): void
    {
        $stadium = (new StadiumService())->toggleActive($this->user(), $request->param('code'));
        $this->done($stadium->isActive ? 'Stadium is now open for bookings.' : 'Stadium deactivated.');
    }

    private function renderForm(Request $request, ?Stadium $stadium): void
    {
        $user = $this->user();
        $this->render($request, 'stadiums/form', [
            'title' => $stadium ? 'Edit ' . $stadium->name : 'New stadium',
            'stadium' => $stadium,
            'cities' => (new CityService())->listActive(),
            'managers' => $user->isAdmin() ? (new UserService())->listManagers() : [],
            'canAssignManager' => $user->isAdmin(),
            'sports' => SportType::options(),
            'surfaces' => SurfaceType::options(),
            'slotOptions' => StadiumRules::SLOT_MINUTES_OPTIONS,
            'map' => AppConfig::map(),
            'needsMap' => true,
        ]);
    }
}
