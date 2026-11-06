<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use App\Constants\Common\ErrorCodes;
use App\Constants\Reservations\ReservationRules;
use App\Dtos\Reservations\BookingRequestDto;
use App\Dtos\Reservations\TimeSlotDto;
use App\Enums\Reservations\BookingUnit;
use App\Enums\Settings\SettingsKey;
use App\Exceptions\Common\AppException;
use App\Exceptions\Common\ConflictException;
use App\Exceptions\Common\ValidationException;
use App\Models\Stadiums\Stadium;
use App\Repositories\Reservations\ReservationRepository;
use App\Services\Settings\SettingsService;
use App\Utilities\Dates\OpeningWindow;
use App\Utilities\Formatting\Money;

/**
 * Turns hourly/daily requests into exact ranges, validates them, detects overlaps, and builds
 * availability, calendars, quotes, and alternative suggestions (docs: workflows/conflict-resolution.md).
 */
final class ConflictService
{
    private const FORMAT = ReservationRules::DATETIME_FORMAT;
    private const STATE_FREE = 'free';
    private const STATE_BOOKED = 'booked';
    private const STATE_PARTIAL = 'partial';
    private const STATE_FULL = 'full';
    private const STATE_PAST = 'past';

    public function __construct(
        private readonly ReservationRepository $reservations = new ReservationRepository(),
        private readonly SettingsService $settings = new SettingsService()
    ) {
    }

    /** Hourly: date + time + minutes. Daily: from the opening of the first day to the closing of the last day. */
    public function resolve(Stadium $stadium, BookingRequestDto $request): TimeSlotDto
    {
        if ($request->unit === BookingUnit::DAY) {
            if (!$stadium->allowsDailyBooking()) {
                throw ValidationException::forField('bookingUnit', 'This stadium is rented by the hour only.');
            }
            $first = new \DateTimeImmutable($request->date);
            $last = $first->modify('+' . ($request->days - 1) . ' days');
            return new TimeSlotDto(
                OpeningWindow::forDate($first, $stadium->openingTime, $stadium->closingTime)->opensAt,
                OpeningWindow::forDate($last, $stadium->openingTime, $stadium->closingTime)->closesAt,
                BookingUnit::DAY,
                (float) $request->days
            );
        }
        $start = new \DateTimeImmutable($request->date . ' ' . $request->startTime);
        return new TimeSlotDto(
            $start,
            $start->modify("+{$request->durationMinutes} minutes"),
            BookingUnit::HOUR,
            round($request->durationMinutes / 60, 2)
        );
    }

    /** Rejects ranges that can never be booked, before any locking. */
    public function validateSlot(Stadium $stadium, TimeSlotDto $slot): void
    {
        $now = new \DateTimeImmutable();
        $leadMinutes = $this->settings->int(SettingsKey::MIN_LEAD_MINUTES);
        if ($slot->startAt < $now->modify("+$leadMinutes minutes")) {
            throw new AppException(ErrorCodes::RESERVATION_IN_PAST, "Choose a start at least $leadMinutes minutes from now.", 422);
        }
        $maxAdvance = $this->settings->int(SettingsKey::MAX_ADVANCE_DAYS);
        if ($slot->startAt > $now->modify("+$maxAdvance days")) {
            throw ValidationException::forField('date', "Bookings open $maxAdvance days in advance.");
        }
        $slot->unit === BookingUnit::DAY ? $this->validateDaily($stadium, $slot) : $this->validateHourly($stadium, $slot);
    }

    /** Must run inside a transaction after the stadium row is locked. 409 with conflicts and suggestions. */
    public function assertNoConflict(Stadium $stadium, TimeSlotDto $slot, ?int $excludeReservationId = null): void
    {
        $conflicts = $this->findConflicts($stadium, $slot, $excludeReservationId);
        if ($conflicts === []) {
            return;
        }
        throw new ConflictException('This time overlaps another booking.', ErrorCodes::RESERVATION_CONFLICT, [
            'conflicts' => $conflicts,
            'suggestions' => $this->suggestAlternatives($stadium, $slot, $excludeReservationId),
        ]);
    }

    /** Price for a resolved range at the given unit price. */
    public function priceFor(TimeSlotDto $slot, float $unitPrice): float
    {
        return Money::round($unitPrice * $slot->unitCount);
    }

    public function unitPriceFor(Stadium $stadium, BookingUnit $unit): float
    {
        return $unit === BookingUnit::DAY ? (float) $stadium->pricePerDay : $stadium->pricePerHour;
    }

    /**
     * Read-only check for the booking widget: validates, prices, and reports conflicts without locking.
     * The locked check in ReservationService::create() still decides.
     */
    public function quote(Stadium $stadium, BookingRequestDto $request): array
    {
        $slot = $this->resolve($stadium, $request);
        $this->validateSlot($stadium, $slot);
        $conflicts = $this->findConflicts($stadium, $slot, null);
        return [
            ...$this->describeSlot($slot),
            'unitPrice' => $this->unitPriceFor($stadium, $slot->unit),
            'total' => $this->priceFor($slot, $this->unitPriceFor($stadium, $slot->unit)),
            'totalLabel' => Money::format($this->priceFor($slot, $this->unitPriceFor($stadium, $slot->unit))),
            'isAvailable' => $conflicts === [],
            'conflicts' => $conflicts,
            'suggestions' => $conflicts === [] ? [] : $this->suggestAlternatives($stadium, $slot, null),
        ];
    }

    /** Hourly slot grid of one opening window. One query loads the window's bookings. */
    public function availability(Stadium $stadium, \DateTimeImmutable $date): array
    {
        $window = OpeningWindow::forDate($date, $stadium->openingTime, $stadium->closingTime);
        $booked = $this->loadBlockingRanges($stadium->id, $window->opensAt, $window->closesAt, null);
        $earliest = $this->earliestStart();

        $slots = [];
        for ($start = $window->opensAt; $start < $window->closesAt; $start = $start->modify("+{$stadium->slotMinutes} minutes")) {
            $end = $start->modify("+{$stadium->slotMinutes} minutes");
            $slots[] = [
                'startAt' => $start->format('Y-m-d H:i'),
                'endAt' => $end->format('Y-m-d H:i'),
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'state' => match (true) {
                    $start < $earliest => self::STATE_PAST,
                    $this->overlapsAny($booked, $start, $end) => self::STATE_BOOKED,
                    default => self::STATE_FREE,
                },
            ];
        }
        return $slots;
    }

    /**
     * Month calendar: each day is free, partial, full, or past, from booked minutes inside its opening window.
     * @return array<int, array{date: string, day: int, state: string, bookedMinutes: int, openMinutes: int}>
     */
    public function calendar(Stadium $stadium, string $month): array
    {
        $first = new \DateTimeImmutable("$month-01");
        $last = $first->modify('last day of this month');
        $lastWindow = OpeningWindow::forDate($last, $stadium->openingTime, $stadium->closingTime);
        $booked = $this->loadBlockingRanges($stadium->id, $first, $lastWindow->closesAt, null);
        $today = new \DateTimeImmutable('today');

        $days = [];
        for ($day = $first; $day <= $last; $day = $day->modify('+1 day')) {
            $window = OpeningWindow::forDate($day, $stadium->openingTime, $stadium->closingTime);
            $bookedMinutes = $this->bookedMinutes($booked, $window->opensAt, $window->closesAt);
            $days[] = [
                'date' => $day->format('Y-m-d'),
                'day' => (int) $day->format('j'),
                'weekday' => (int) $day->format('N'),
                'bookedMinutes' => $bookedMinutes,
                'openMinutes' => $window->minutes(),
                'state' => match (true) {
                    $day < $today => self::STATE_PAST,
                    $bookedMinutes >= $window->minutes() => self::STATE_FULL,
                    $bookedMinutes > 0 => self::STATE_PARTIAL,
                    default => self::STATE_FREE,
                },
            ];
        }
        return $days;
    }

    /**
     * Free alternatives with the same size. Hourly: same day closest first, then the same time on following days.
     * Daily: the following start dates.
     */
    public function suggestAlternatives(Stadium $stadium, TimeSlotDto $requested, ?int $excludeReservationId = null): array
    {
        $limit = $this->settings->int(SettingsKey::SUGGESTION_LIMIT);
        $searchDays = ReservationRules::SUGGESTION_SEARCH_DAYS;
        $from = $requested->startAt->setTime(0, 0)->modify('-1 day');
        $to = $requested->endAt->modify('+' . ($searchDays + 1) . ' days');
        $booked = $this->loadBlockingRanges($stadium->id, $from, $to, $excludeReservationId);

        $suggestions = [];
        foreach ($this->candidates($stadium, $requested, $searchDays) as $candidate) {
            if (!$this->isBookable($stadium, $candidate) || $this->overlapsAny($booked, $candidate->startAt, $candidate->endAt)) {
                continue;
            }
            $suggestions[] = $this->describeSlot($candidate);
            if (count($suggestions) >= $limit) {
                break;
            }
        }
        return $suggestions;
    }

    /** Fields the client needs to show a range and to re-submit it as a booking request. */
    public function describeSlot(TimeSlotDto $slot): array
    {
        $isDaily = $slot->unit === BookingUnit::DAY;
        $sameDay = $slot->startAt->format('Y-m-d') === $slot->endAt->format('Y-m-d');
        return [
            'bookingUnit' => $slot->unit->value,
            'date' => $slot->startAt->format('Y-m-d'),
            'startTime' => $slot->startAt->format('H:i'),
            'durationMinutes' => $slot->durationMinutes(),
            'days' => $isDaily ? (int) $slot->unitCount : 0,
            'startAt' => $slot->startAt->format('Y-m-d H:i'),
            'endAt' => $slot->endAt->format('Y-m-d H:i'),
            'size' => $slot->unit->describe($slot->unitCount),
            'label' => $isDaily
                ? $slot->startAt->format('D j M') . ' → ' . $slot->endAt->format('D j M, H:i')
                : $slot->startAt->format('D j M, H:i') . ' – ' . $slot->endAt->format($sameDay ? 'H:i' : 'D H:i'),
        ];
    }

    private function validateHourly(Stadium $stadium, TimeSlotDto $slot): void
    {
        $minutes = $slot->durationMinutes();
        $maxHours = $this->settings->int(SettingsKey::MAX_HOURS_PER_BOOKING);
        if ($minutes % $stadium->slotMinutes !== 0) {
            throw ValidationException::forField('durationMinutes', "Duration must be a multiple of {$stadium->slotMinutes} minutes.");
        }
        if ($minutes > $maxHours * 60) {
            throw ValidationException::forField('durationMinutes', "Hourly bookings can last up to $maxHours hours. Book full days for longer events.");
        }
        if (!OpeningWindow::contains($slot->startAt, $slot->endAt, $stadium->openingTime, $stadium->closingTime)) {
            throw new AppException(ErrorCodes::OUTSIDE_OPENING_HOURS, "This time is outside opening hours ({$stadium->hoursLabel()}).", 422);
        }
        if (!$this->isAligned($stadium, $slot->startAt)) {
            throw ValidationException::forField('startTime', "Start time must follow the {$stadium->slotMinutes}-minute grid.");
        }
    }

    private function validateDaily(Stadium $stadium, TimeSlotDto $slot): void
    {
        if (!$stadium->allowsDailyBooking()) {
            throw ValidationException::forField('bookingUnit', 'This stadium is rented by the hour only.');
        }
        $maxDays = $this->settings->int(SettingsKey::MAX_DAYS_PER_BOOKING);
        if ($slot->unitCount > $maxDays) {
            throw ValidationException::forField('days', "Daily bookings can last up to $maxDays days.");
        }
    }

    private function findConflicts(Stadium $stadium, TimeSlotDto $slot, ?int $excludeId): array
    {
        $rows = $this->reservations->findOverlapping($stadium->id, $slot->startAt->format(self::FORMAT), $slot->endAt->format(self::FORMAT), $excludeId);
        return array_map(static fn (array $range) => [
            'startAt' => substr($range['start_at'], 0, 16),
            'endAt' => substr($range['end_at'], 0, 16),
        ], $rows);
    }

    /** @return TimeSlotDto[] */
    private function candidates(Stadium $stadium, TimeSlotDto $requested, int $searchDays): array
    {
        $candidates = [];
        if ($requested->unit === BookingUnit::HOUR) {
            $window = OpeningWindow::forDate($requested->startAt, $stadium->openingTime, $stadium->closingTime);
            $minutes = $requested->durationMinutes();
            for ($start = $window->opensAt; $start < $window->closesAt; $start = $start->modify("+{$stadium->slotMinutes} minutes")) {
                if ($start != $requested->startAt) {
                    $candidates[] = $requested->withStart($start, $start->modify("+$minutes minutes"));
                }
            }
            $target = $requested->startAt->getTimestamp();
            usort($candidates, static fn ($a, $b) => abs($a->startAt->getTimestamp() - $target) <=> abs($b->startAt->getTimestamp() - $target));
        }
        for ($offset = 1; $offset <= $searchDays; $offset++) {
            $candidates[] = $requested->withStart($requested->startAt->modify("+$offset days"), $requested->endAt->modify("+$offset days"));
        }
        return $candidates;
    }

    private function isBookable(Stadium $stadium, TimeSlotDto $slot): bool
    {
        try {
            $this->validateSlot($stadium, $slot);
            return true;
        } catch (AppException) {
            return false;
        }
    }

    private function isAligned(Stadium $stadium, \DateTimeImmutable $start): bool
    {
        foreach ([$start, $start->modify('-1 day')] as $day) {
            $window = OpeningWindow::forDate($day, $stadium->openingTime, $stadium->closingTime);
            if ($start >= $window->opensAt && $start < $window->closesAt) {
                return intdiv($start->getTimestamp() - $window->opensAt->getTimestamp(), 60) % $stadium->slotMinutes === 0;
            }
        }
        return false;
    }

    private function earliestStart(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())->modify('+' . $this->settings->int(SettingsKey::MIN_LEAD_MINUTES) . ' minutes');
    }

    /** @return array<int, array{0: int, 1: int}> [startTimestamp, endTimestamp] */
    private function loadBlockingRanges(int $stadiumId, \DateTimeImmutable $from, \DateTimeImmutable $to, ?int $excludeId): array
    {
        $rows = $this->reservations->findOverlapping($stadiumId, $from->format(self::FORMAT), $to->format(self::FORMAT), $excludeId);
        return array_map(static fn (array $row) => [strtotime($row['start_at']), strtotime($row['end_at'])], $rows);
    }

    private function overlapsAny(array $ranges, \DateTimeImmutable $start, \DateTimeImmutable $end): bool
    {
        return $this->bookedMinutes($ranges, $start, $end) > 0;
    }

    /** Minutes of [start, end) covered by the ranges (ranges never overlap each other). */
    private function bookedMinutes(array $ranges, \DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        $startTs = $start->getTimestamp();
        $endTs = $end->getTimestamp();
        $seconds = 0;
        foreach ($ranges as [$bookedStart, $bookedEnd]) {
            $seconds += max(0, min($endTs, $bookedEnd) - max($startTs, $bookedStart));
        }
        return intdiv($seconds, 60);
    }
}
