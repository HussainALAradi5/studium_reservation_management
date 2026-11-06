<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Enums\Users\Gender;
use App\Enums\Users\UserRole;
use App\Validations\Users\UserValidation;

/** Admin create/edit user. Password is required on create and optional on edit. */
final class UserFormDto
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $username,
        public readonly string $email,
        public readonly string $phone,
        public readonly UserRole $role,
        public readonly ?Gender $gender,
        public readonly ?string $birthDate,
        public readonly bool $isActive,
        public readonly ?string $password
    ) {
    }

    public static function fromInput(array $input, bool $isCreate): self
    {
        $validator = UserValidation::identity($input);
        UserValidation::username($validator);
        UserValidation::personal($validator);
        $validator->enum('role', 'role', UserRole::class)->boolean('isActive');
        if ($isCreate || ($input['password'] ?? '') !== '') {
            UserValidation::newPassword($validator, $input, 'password', 'passwordConfirmation');
        }
        $data = $validator->validated();
        return new self(
            $data['fullName'],
            $data['username'],
            $data['email'],
            $data['phone'],
            $data['role'],
            $data['gender'],
            $data['birthDate'],
            $data['isActive'],
            isset($data['password']) ? (string) $input['password'] : null
        );
    }
}
