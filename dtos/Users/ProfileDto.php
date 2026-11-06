<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Enums\Users\Gender;
use App\Validations\Users\UserValidation;

/** Fields a user may change on their own profile (not role, username, or status). */
final class ProfileDto
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $email,
        public readonly string $phone,
        public readonly ?Gender $gender,
        public readonly ?string $birthDate
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = UserValidation::identity($input);
        UserValidation::personal($validator);
        $data = $validator->validated();
        return new self($data['fullName'], $data['email'], $data['phone'], $data['gender'], $data['birthDate']);
    }
}
