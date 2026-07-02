<?php

namespace App\Enums\User;

enum UserType: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case STUDENT = 'student';
}
