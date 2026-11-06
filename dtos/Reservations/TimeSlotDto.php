<?php

declare(strict_types=1);

namespace App\Dtos\Reservations;

use App\Enums\Reservations\BookingUnit;

/** A resolved, exact rental range [startAt, endAt) with its unit and size (hours or days). */
final class TimeSlotDto
{
    public function __construct(
        public readonly \DateTimeImmutable $startAt,
        public readonly \DateTimeImmutable $endAt,
        public readonly BookingUnit $unit,
        public readonly float $unitCount
    ) {
    }

    public function durationMinutes(): int
    {
        return intdiv($this->endAt->getTimestamp() - $this->startAt->getTimestamp(), 60);
    }

    /** Same unit and size, moved to a new start (used for suggestions). */
    public function withStart(\DateTimeImmutable $startAt, \DateTimeImmutable $endAt): self
    {
        return new self($startAt, $endAt, $this->unit, $this->unitCount);
    }
}
