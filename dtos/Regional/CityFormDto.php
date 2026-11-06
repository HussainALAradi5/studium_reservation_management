<?php

declare(strict_types=1);

namespace App\Dtos\Regional;

use App\Constants\Common\RegexPatterns;
use App\Constants\Stadiums\StadiumRules;
use App\Validations\Common\Validator;

final class CityFormDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $countryCode,
        public readonly bool $isActive
    ) {
    }

    public static function fromInput(array $input): self
    {
        $input['countryCode'] = strtoupper(trim((string) ($input['countryCode'] ?? StadiumRules::DEFAULT_COUNTRY_CODE)));
        $data = (new Validator($input))
            ->string('name', 'City name', 2, 80, true, RegexPatterns::PLACE_NAME)
            ->string('countryCode', 'Country code', 2, 2, true, RegexPatterns::COUNTRY_CODE)
            ->boolean('isActive')
            ->validated();
        return new self($data['name'], $data['countryCode'], $data['isActive']);
    }
}
