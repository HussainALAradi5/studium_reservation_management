<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Validations\Users\UserValidation;

/** Self-registration input; the role is always customer and is not read from input. */
final class RegisterDto
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $username,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = UserValidation::identity($input);
        UserValidation::username($validator);
        UserValidation::newPassword($validator, $input, 'password', 'passwordConfirmation');
        $data = $validator->validated();
        return new self($data['fullName'], $data['username'], $data['email'], $data['phone'], (string) $input['password']);
    }
}
