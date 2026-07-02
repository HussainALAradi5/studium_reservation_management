<?php

namespace App\Models\User;

use App\Enums\User\Gender;
use App\Enums\User\UserType;
use App\utilities\Security;

class User
{
    public function __construct(
        public int $code,
        public string $userName,
        public string $password,
        public string $email,
        public string $phoneNumber,
        public string $secondPhoneNumber = '',
        public string $birthDay,
        public bool $isActive,
        public Gender $gender,
        public UserType $userType,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public function toDatabaseParams(bool $hashPassword = true): array
    {
        return [
            'userName'          => $this->userName,
            'password'          => $hashPassword ? Security::hashPassword($this->password) : $this->password,
            'email'             => $this->email,
            'phoneNumber'       => $this->phoneNumber,
            'secondPhoneNumber' => $this->secondPhoneNumber,
            'birthDay'          => $this->birthDay,
            'isActive'          => (int)$this->isActive,
            'gender'            => $this->gender->value,
            'userType'          => $this->userType->value
        ];
    }
};
