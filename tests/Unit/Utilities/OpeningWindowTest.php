<?php

declare(strict_types=1);

namespace Tests\Unit\Utilities;

use App\Utilities\Dates\OpeningWindow;
use Tests\TestCase;

final class OpeningWindowTest extends TestCase
{
    public function testSameDayWindow(): void
    {
        $window = OpeningWindow::forDate(new \DateTimeImmutable('2030-01-10'), '08:00', '23:00');
        $this->assertSame('2030-01-10 08:00', $window->opensAt->format('Y-m-d H:i'));
        $this->assertSame('2030-01-10 23:00', $window->closesAt->format('Y-m-d H:i'));
        $this->assertSame(900, $window->minutes());
    }

    public function testWindowCrossingMidnightEndsNextDay(): void
    {
        $window = OpeningWindow::forDate(new \DateTimeImmutable('2030-01-10'), '16:00', '02:00');
        $this->assertSame('2030-01-11 02:00', $window->closesAt->format('Y-m-d H:i'));
    }

    public function testContainsAfterMidnightSlotOfPreviousDay(): void
    {
        $start = new \DateTimeImmutable('2030-01-11 01:00');
        $this->assertTrue(OpeningWindow::contains($start, $start->modify('+1 hour'), '16:00', '02:00'));
    }

    public function testRejectsRangeOutsideHours(): void
    {
        $start = new \DateTimeImmutable('2030-01-10 07:00');
        $this->assertFalse(OpeningWindow::contains($start, $start->modify('+1 hour'), '08:00', '23:00'));
        $late = new \DateTimeImmutable('2030-01-10 22:30');
        $this->assertFalse(OpeningWindow::contains($late, $late->modify('+1 hour'), '08:00', '23:00'), 'may not run past closing');
    }
}
