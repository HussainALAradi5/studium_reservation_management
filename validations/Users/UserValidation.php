<?php

declare(strict_types=1);

namespace App\Validations\Users;

use App\Constants\Common\RegexPatterns;
use App\Constants\Users\AuthRules;
use App\Enums\Users\Gender;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Security\PasswordHasher;
use App\Validations\Common\Validator;

/** User field rules shared by registration, admin user forms, and profile updates. */
final class UserValidation
{
    /** Identity fields are normalized first, so "Ali.K" and "ali.k" are the same username. */
    public static function identity(array $input): Validator
    {
        return (new Validator(IdentityNormalizer::userInput($input)))
            ->string('fullName', 'Full name', 3, 120, true, RegexPatterns::PERSON_NAME)
            ->email('email')
            ->string('phone', 'Phone', 8, 16, true, RegexPatterns::PHONE);
    }

    public static function username(Validator $validator): void
    {
        $validator->string('username', 'Username', 3, 40, true, RegexPatterns::USERNAME);
    }

    public static function personal(Validator $validator): void
    {
        $validator
            ->enum('gender', 'gender', Gender::class, false)
            ->date('birthDate', 'Birth date', false, false);
    }

    /** Raw password is kept as typed (no trimming) and checked for strength and confirmation. */
    public static function newPassword(Validator $validator, array $input, string $field, string $confirmationField): void
    {
        $password = (string) ($input[$field] ?? '');
        $validator->string($field, 'Password', AuthRules::PASSWORD_MIN_LENGTH, 255);
        if ($password !== '' && !PasswordHasher::isStrong($password)) {
            $validator->addError($field, 'Use at least ' . AuthRules::PASSWORD_MIN_LENGTH . ' characters with a letter and a digit.');
        }
        if ($password !== (string) ($input[$confirmationField] ?? '')) {
            $validator->addError($confirmationField, 'Passwords do not match.');
        }
    }
}
