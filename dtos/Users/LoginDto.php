<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Utilities\Formatting\IdentityNormalizer;
use App\Validations\Common\Validator;

final class LoginDto
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $password,
        public readonly string $ip
    ) {
    }

    public static function fromInput(array $input, string $ip): self
    {
        $data = (new Validator(IdentityNormalizer::userInput($input)))
            ->string('identifier', 'Username or email', 3, 160)
            ->string('password', 'Password', 1, 255)
            ->validated();
        return new self($data['identifier'], (string) ($input['password'] ?? ''), $ip);
    }
}
