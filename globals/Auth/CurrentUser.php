<?php

declare(strict_types=1);

namespace App\Globals\Auth;

use App\Models\Users\User;

/** The signed-in user for this request; read by repositories to fill audit fields. */
final class CurrentUser
{
    private static ?User $user = null;

    public static function set(?User $user): void
    {
        self::$user = $user;
    }

    public static function get(): ?User
    {
        return self::$user;
    }

    public static function id(): ?int
    {
        return self::$user?->id;
    }
}
