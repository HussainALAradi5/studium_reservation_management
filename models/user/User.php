<?php

namespace App\Models\User;

use App\Enums\User\Gender;
use App\Enums\User\UserType;
use App\utilities\Security;

class User
{
    public function __construct(
        public int $id,
        public string $code,
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

    public static function generateCode(int $nextSequence): string
    {
        return sprintf('USER/%s/%05d', date('Y'), $nextSequence);
    }


    public function toDatabaseParams(bool $hashPassword = true): array
    {
        return [
            'code'              => $this->code,
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


    // In User.php
    public static function fromRegistration(
        string $userName,
        string $password,
        string $email,
        string $phoneNumber,
        Gender $gender,
        UserType $userType,
        string $code = '' 
    ): self {
        return new self(
            0,
            $code,
            $userName,
            $password,
            $email,
            $phoneNumber,
            '',
            '',
            true,
            $gender,
            $userType,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        );
    }

    public static function fromDatabase(array $data): self
    {
        return new self(
            (int)$data['id'],
            (string)$data['code'],
            $data['userName'],
            $data['password'],
            $data['email'],
            $data['phoneNumber'],
            $data['secondPhoneNumber'] ?? '',
            $data['birthDay'],
            (bool)$data['isActive'],
            Gender::from($data['gender']),
            UserType::from($data['userType']),
            $data['createdAt'],
            $data['updatedAt']
        );
    }
};
