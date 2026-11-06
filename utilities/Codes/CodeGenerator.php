<?php

declare(strict_types=1);

namespace App\Utilities\Codes;

use App\Constants\Common\CodePrefixes;

/** Generates public record codes such as STD-7KQ2M9XA. */
final class CodeGenerator
{
    public static function generate(string $prefix): string
    {
        $alphabet = CodePrefixes::ALPHABET;
        $max = strlen($alphabet) - 1;
        $token = '';
        for ($i = 0; $i < CodePrefixes::LENGTH; $i++) {
            $token .= $alphabet[random_int(0, $max)];
        }
        return $prefix . '-' . $token;
    }
}
