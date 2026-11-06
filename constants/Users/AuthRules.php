<?php

declare(strict_types=1);

namespace App\Constants\Users;

final class AuthRules
{
    public const MAX_LOGIN_ATTEMPTS = 5;
    public const LOGIN_WINDOW_MINUTES = 15;
    public const PASSWORD_MIN_LENGTH = 8;
    public const SESSION_USER_KEY = 'auth_user_id';
    public const SESSION_ACTIVITY_KEY = 'auth_last_activity';
}
