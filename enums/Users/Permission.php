<?php

declare(strict_types=1);

namespace App\Enums\Users;

use App\Enums\Common\HasOptions;

/**
 * Actions guarded by the `can:` route middleware. Which roles hold which permission is stored in
 * role_permissions (editable by admins); admins always hold every permission.
 */
enum Permission: string
{
    use HasOptions;

    case RESERVATIONS_BOOK = 'reservations.book';
    case RESERVATIONS_MANAGE = 'reservations.manage';
    case PAYMENTS_RECORD = 'payments.record';
    case STADIUMS_MANAGE = 'stadiums.manage';
    case STADIUMS_CREATE = 'stadiums.create';
    case EXPENSES_MANAGE = 'expenses.manage';
    case REPORTS_VIEW = 'reports.view';
    case CITIES_MANAGE = 'cities.manage';
    case USERS_MANAGE = 'users.manage';
    case SETTINGS_MANAGE = 'settings.manage';
    case REVIEWS_WRITE = 'reviews.write';
    case REVIEWS_MODERATE = 'reviews.moderate';

    public function label(): string
    {
        return match ($this) {
            self::RESERVATIONS_BOOK => 'Book stadiums',
            self::RESERVATIONS_MANAGE => 'Confirm, reject & complete bookings',
            self::PAYMENTS_RECORD => 'Record payments & refunds',
            self::STADIUMS_MANAGE => 'Edit assigned stadiums',
            self::STADIUMS_CREATE => 'Create stadiums & assign managers',
            self::EXPENSES_MANAGE => 'Manage expenses',
            self::REPORTS_VIEW => 'View accounting reports',
            self::CITIES_MANAGE => 'Manage cities',
            self::USERS_MANAGE => 'Manage users',
            self::SETTINGS_MANAGE => 'Manage settings & permissions',
            self::REVIEWS_WRITE => 'Rate stadiums they rented',
            self::REVIEWS_MODERATE => 'Remove reviews of their stadiums',
        };
    }

    /** Defaults written by the seed (docs: security/README.md#permission-matrix). */
    public static function defaultsFor(UserRole $role): array
    {
        return match ($role) {
            UserRole::ADMIN => self::cases(),
            UserRole::MANAGER => [
                self::RESERVATIONS_MANAGE,
                self::PAYMENTS_RECORD,
                self::STADIUMS_MANAGE,
                self::EXPENSES_MANAGE,
                self::REPORTS_VIEW,
                self::REVIEWS_MODERATE,
            ],
            UserRole::CUSTOMER => [self::RESERVATIONS_BOOK, self::REVIEWS_WRITE],
        };
    }
}
