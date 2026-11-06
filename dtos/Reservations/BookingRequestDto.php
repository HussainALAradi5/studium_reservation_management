<?php

declare(strict_types=1);

namespace App\Dtos\Reservations;

use App\Constants\Reservations\ReservationRules;
use App\Enums\Reservations\BookingUnit;
use App\Validations\Common\Validator;

/**
 * What the user asked for. Hourly: date + start time + minutes. Daily: start date + number of days.
 * ConflictService::resolve() turns it into exact start/end times using the stadium's hours.
 */
final class BookingRequestDto
{
    public function __construct(
        public readonly BookingUnit $unit,
        public readonly string $date,
        public readonly ?string $startTime,
        public readonly int $durationMinutes,
        public readonly int $days
    ) {
    }

    /** Adds the booking-size rules for the chosen unit to $validator. */
    public static function validate(Validator $validator, array $input): Validator
    {
        $unit = BookingUnit::tryFrom((string) ($input['bookingUnit'] ?? BookingUnit::HOUR->value));
        $validator->enum('bookingUnit', 'booking type', BookingUnit::class, false)->date('date', 'Date');
        if ($unit === BookingUnit::DAY) {
            return $validator->integer('days', 'Number of days', 1, 365);
        }
        return $validator
            ->time('startTime', 'Start time')
            ->integer('durationMinutes', 'Duration', ReservationRules::MIN_HOURLY_MINUTES, 24 * 60);
    }

    public static function fromValidated(array $data): self
    {
        $unit = $data['bookingUnit'] ?? BookingUnit::HOUR;
        return new self(
            $unit,
            $data['date'],
            $unit === BookingUnit::HOUR ? $data['startTime'] : null,
            $unit === BookingUnit::HOUR ? $data['durationMinutes'] : 0,
            $unit === BookingUnit::DAY ? $data['days'] : 0
        );
    }

    public static function fromInput(array $input): self
    {
        return self::fromValidated(self::validate(new Validator($input), $input)->validated());
    }
}
