<?php

declare(strict_types=1);

namespace App\Dtos\Stadiums;

use App\Constants\Common\RegexPatterns;
use App\Constants\Stadiums\StadiumRules;
use App\Enums\Stadiums\SportType;
use App\Enums\Stadiums\SurfaceType;
use App\Validations\Common\Validator;

/** Stadium details plus its address; managerCode is applied only when an admin submits. */
final class StadiumFormDto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly SportType $sportType,
        public readonly SurfaceType $surfaceType,
        public readonly int $capacity,
        public readonly float $pricePerHour,
        public readonly ?float $pricePerDay,
        public readonly string $openingTime,
        public readonly string $closingTime,
        public readonly int $slotMinutes,
        public readonly bool $isActive,
        public readonly string $managerCode,
        public readonly string $cityCode,
        public readonly string $street,
        public readonly ?string $building,
        public readonly ?string $block,
        public readonly ?string $area,
        public readonly ?float $latitude,
        public readonly ?float $longitude
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = (new Validator($input))
            ->string('name', 'Name', 3, 120, true, RegexPatterns::STADIUM_NAME)
            ->string('description', 'Description', 1, StadiumRules::MAX_DESCRIPTION_LENGTH, false)
            ->enum('sportType', 'sport', SportType::class)
            ->enum('surfaceType', 'surface', SurfaceType::class)
            ->integer('capacity', 'Capacity', StadiumRules::MIN_CAPACITY, StadiumRules::MAX_CAPACITY)
            ->decimal('pricePerHour', 'Price per hour', 0.001, 10000)
            ->decimal('pricePerDay', 'Price per day', 0.001, 100000, false)
            ->time('openingTime', 'Opening time')
            ->time('closingTime', 'Closing time')
            ->inList('slotMinutes', 'slot length', StadiumRules::SLOT_MINUTES_OPTIONS)
            ->boolean('isActive')
            ->string('managerCode', 'Manager', 12, 12, false, RegexPatterns::RECORD_CODE)
            ->string('cityCode', 'City', 12, 12, true, RegexPatterns::RECORD_CODE)
            ->string('street', 'Street', 2, 120, true, RegexPatterns::STREET)
            ->string('building', 'Building', 1, 20, false, RegexPatterns::BUILDING_NUMBER)
            ->string('block', 'Block', 1, 20, false, RegexPatterns::BUILDING_NUMBER)
            ->string('area', 'Area', 2, 80, false, RegexPatterns::PLACE_NAME)
            ->decimal('latitude', 'Latitude', -90, 90, false)
            ->decimal('longitude', 'Longitude', -180, 180, false);

        if ($validator->has('latitude') !== $validator->has('longitude')) {
            $validator->addError('latitude', 'Pick the location on the map or clear both coordinates.');
        }
        if ($validator->value('openingTime') !== null && $validator->value('openingTime') === $validator->value('closingTime')) {
            $validator->addError('closingTime', 'Closing time must differ from opening time.');
        }

        $data = $validator->validated();
        return new self(
            $data['name'],
            $data['description'],
            $data['sportType'],
            $data['surfaceType'],
            $data['capacity'],
            round($data['pricePerHour'], 3),
            $data['pricePerDay'] === null ? null : round($data['pricePerDay'], 3),
            $data['openingTime'],
            $data['closingTime'],
            (int) $data['slotMinutes'],
            $data['isActive'],
            (string) $data['managerCode'],
            $data['cityCode'],
            $data['street'],
            $data['building'],
            $data['block'],
            $data['area'],
            $data['latitude'] === null ? null : round($data['latitude'], 7),
            $data['longitude'] === null ? null : round($data['longitude'], 7)
        );
    }
}
