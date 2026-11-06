<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Validations\Common\Validator;
use App\Validations\Users\UserValidation;

final class ChangePasswordDto
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = (new Validator($input))->string('currentPassword', 'Current password', 1, 255);
        UserValidation::newPassword($validator, $input, 'newPassword', 'newPasswordConfirmation');
        $data = $validator->validated();
        return new self((string) $input['currentPassword'], (string) $input['newPassword']);
    }
}
