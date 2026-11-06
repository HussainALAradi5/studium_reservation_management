<?php

declare(strict_types=1);

namespace App\Utilities\Dates;

/**
 * Opening window of a stadium for a given date. When closing <= opening the window ends the next day
 * (e.g. 16:00 → 02:00).
 */
final class OpeningWindow
{
    private function __construct(
        public readonly \DateTimeImmutable $opensAt,
        public readonly \DateTimeImmutable $closesAt
    ) {
    }

    public static function forDate(\DateTimeImmutable $date, string $openingTime, string $closingTime): self
    {
        $day = $date->format('Y-m-d');
        $opensAt = new \DateTimeImmutable("$day $openingTime");
        $closesAt = new \DateTimeImmutable("$day $closingTime");
        if ($closesAt <= $opensAt) {
            $closesAt = $closesAt->modify('+1 day');
        }
        return new self($opensAt, $closesAt);
    }

    /** True when [start, end) fits in the window of start's date or of the previous day (after-midnight hours). */
    public static function contains(\DateTimeImmutable $start, \DateTimeImmutable $end, string $openingTime, string $closingTime): bool
    {
        foreach ([$start, $start->modify('-1 day')] as $day) {
            $window = self::forDate($day, $openingTime, $closingTime);
            if ($start >= $window->opensAt && $end <= $window->closesAt) {
                return true;
            }
        }
        return false;
    }

    public function minutes(): int
    {
        return intdiv($this->closesAt->getTimestamp() - $this->opensAt->getTimestamp(), 60);
    }
}
