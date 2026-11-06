<?php

declare(strict_types=1);

namespace App\Dtos\Reservations;

use App\Constants\Common\RegexPatterns;
use App\Validations\Common\Validator;

/** Customer booking request. Price and status are never taken from input. */
final class CreateReservationDto
{
    public function __construct(
        public readonly string $stadiumCode,
        public readonly BookingRequestDto $request,
        public readonly ?string $notes
    ) {
    }

    public static function fromInput(array $input): self
    {
        $validator = (new Validator($input))
            ->string('stadiumCode', 'Stadium', 12, 12, true, RegexPatterns::RECORD_CODE)
            ->string('notes', 'Notes', 1, 500, false);
        $data = BookingRequestDto::validate($validator, $input)->validated();
        return new self($data['stadiumCode'], BookingRequestDto::fromValidated($data), $data['notes']);
    }
}
