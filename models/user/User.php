<?php

namespace App\Models\User;

use App\Enums\User\Gender;
use App\Enums\User\UserType;

class User
{
    public function __construct(
        public int $code,
        public string $userName,
        public string $password,
        public string $birthDay,
        public bool $isActive,
        public Gender $gender,
        public UserType $userType,
        public string $createdAt,
        public string $updatedAt,
    ) {}
};
