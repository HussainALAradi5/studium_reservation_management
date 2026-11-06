<?php

declare(strict_types=1);

use App\Controllers\Accounting\ExpenseController;
use App\Controllers\Accounting\PaymentController;
use App\Controllers\Accounting\ReportController;
use App\Controllers\Home\HomeController;
use App\Controllers\Notifications\NotificationController;
use App\Controllers\Regional\CityController;
use App\Controllers\Reservations\ReservationController;
use App\Controllers\Reviews\ReviewController;
use App\Controllers\Settings\SettingsController;
use App\Controllers\Stadiums\StadiumController;
use App\Controllers\Users\AuthController;
use App\Controllers\Users\ProfileController;
use App\Controllers\Users\UserController;
use App\Enums\Users\Permission as P;
use App\Utilities\Http\Router;

/**
 * Every route in one place. Pages render HTML; /api/* returns the JSON response shape and is called with fetch.
 * "can:<permission>" checks the role's grants (role_permissions); services then apply ownership scope.
 * The client endpoint map (assets/javascripts/config/endpoints.js) mirrors the /api paths.
 */
return static function (Router $router): void {
    $can = static fn (P ...$permissions) => 'can:' . implode(',', array_map(static fn (P $p) => $p->value, $permissions));

    // Public
    $router->get('/', [HomeController::class, 'landing']);
    $router->get('/stadiums', [StadiumController::class, 'browse']);
    $router->get('/stadiums/{code}', [StadiumController::class, 'show']);
    $router->get('/api/stadiums/{code}/availability', [StadiumController::class, 'availability']);
    $router->get('/api/stadiums/{code}/calendar', [StadiumController::class, 'calendar']);
    $router->get('/api/stadiums/{code}/quote', [StadiumController::class, 'quote']);

    $router->group(['guest'], static function (Router $router): void {
        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->get('/register', [AuthController::class, 'showRegister']);
    });
    $router->post('/api/auth/login', [AuthController::class, 'login']);
    $router->post('/api/auth/register', [AuthController::class, 'register']);

    $router->group(['auth'], static function (Router $router) use ($can): void {
        $router->post('/api/auth/logout', [AuthController::class, 'logout']);
        $router->get('/dashboard', [HomeController::class, 'dashboard']);

        // Notifications (always the signed-in user's own)
        $router->get('/notifications', [NotificationController::class, 'index']);
        $router->get('/api/notifications', [NotificationController::class, 'feed']);
        $router->get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        $router->post('/api/notifications/read-all', [NotificationController::class, 'markAllRead']);
        $router->post('/api/notifications/{code}/read', [NotificationController::class, 'markRead']);

        // Reviews: signed-in users read; customers who played there write once
        $router->get('/api/stadiums/{code}/reviews', [ReviewController::class, 'index']);
        $router->post('/api/stadiums/{code}/reviews', [ReviewController::class, 'store'], [$can(P::REVIEWS_WRITE)]);
        $router->delete('/api/reviews/{code}', [ReviewController::class, 'destroy']);

        $router->get('/profile', [ProfileController::class, 'show']);
        $router->put('/api/profile', [ProfileController::class, 'update']);
        $router->put('/api/profile/password', [ProfileController::class, 'changePassword']);

        // Reservations: list/show/cancel/reschedule are scoped by ownership in the service
        $router->get('/reservations', [ReservationController::class, 'index']);
        $router->get('/reservations/{code}', [ReservationController::class, 'show']);
        $router->post('/api/reservations', [ReservationController::class, 'store'], [$can(P::RESERVATIONS_BOOK)]);
        $router->post('/api/reservations/{code}/cancel', [ReservationController::class, 'cancel']);
        $router->post('/api/reservations/{code}/reschedule', [ReservationController::class, 'reschedule']);
        $router->post('/api/reservations/{code}/confirm', [ReservationController::class, 'confirm'], [$can(P::RESERVATIONS_MANAGE)]);
        $router->post('/api/reservations/{code}/reject', [ReservationController::class, 'reject'], [$can(P::RESERVATIONS_MANAGE)]);
        $router->post('/api/reservations/{code}/complete', [ReservationController::class, 'complete'], [$can(P::RESERVATIONS_MANAGE)]);
        $router->post('/api/reservations/{code}/payments', [PaymentController::class, 'store'], [$can(P::PAYMENTS_RECORD)]);

        // Stadium management
        $router->group([$can(P::STADIUMS_MANAGE)], static function (Router $router): void {
            $router->get('/manage/stadiums', [StadiumController::class, 'manage']);
            $router->get('/manage/stadiums/{code}/edit', [StadiumController::class, 'edit']);
            $router->put('/api/stadiums/{code}', [StadiumController::class, 'update']);
            $router->post('/api/stadiums/{code}/toggle', [StadiumController::class, 'toggle']);
        });
        $router->get('/manage/stadiums/create', [StadiumController::class, 'create'], [$can(P::STADIUMS_CREATE)]);
        $router->post('/api/stadiums', [StadiumController::class, 'store'], [$can(P::STADIUMS_CREATE)]);

        // Accounting
        $router->group([$can(P::EXPENSES_MANAGE)], static function (Router $router): void {
            $router->get('/accounting/expenses', [ExpenseController::class, 'index']);
            $router->post('/api/expenses', [ExpenseController::class, 'store']);
            $router->delete('/api/expenses/{code}', [ExpenseController::class, 'destroy']);
        });
        $router->get('/accounting/reports', [ReportController::class, 'index'], [$can(P::REPORTS_VIEW)]);

        // Administration
        $router->group([$can(P::CITIES_MANAGE)], static function (Router $router): void {
            $router->get('/cities', [CityController::class, 'index']);
            $router->post('/api/cities', [CityController::class, 'store']);
            $router->put('/api/cities/{code}', [CityController::class, 'update']);
            $router->delete('/api/cities/{code}', [CityController::class, 'destroy']);
        });
        $router->group([$can(P::USERS_MANAGE)], static function (Router $router): void {
            $router->get('/users', [UserController::class, 'index']);
            $router->get('/users/create', [UserController::class, 'create']);
            $router->get('/users/{code}/edit', [UserController::class, 'edit']);
            $router->post('/api/users', [UserController::class, 'store']);
            $router->put('/api/users/{code}', [UserController::class, 'update']);
            $router->post('/api/users/{code}/toggle', [UserController::class, 'toggle']);
        });
        $router->group([$can(P::SETTINGS_MANAGE)], static function (Router $router): void {
            $router->get('/settings', [SettingsController::class, 'index']);
            $router->put('/api/settings', [SettingsController::class, 'update']);
            $router->put('/api/settings/permissions/{role}', [SettingsController::class, 'updatePermissions']);
        });
    });
};
