<?php

declare(strict_types=1);

namespace App\Seeds\Demo;

use App\Enums\Reservations\BookingUnit;
use App\Enums\Reservations\PaymentStatus;
use App\Enums\Reservations\ReservationStatus;
use App\Seeds\Stadiums\StadiumsSeed;
use App\Utilities\Security\PasswordHasher;

/**
 * Turns DemoPeople and DemoActivity into SQL for a phpMyAdmin import. Every insert uses a fixed code and
 * INSERT IGNORE, so importing twice changes nothing; dates are computed by MySQL from CURDATE(), and a
 * booking is skipped when it would overlap an existing pending or confirmed booking of that stadium.
 */
final class DemoSqlBuilder
{
    private const PASSWORD_LENGTH = 12;
    private const PASSWORD_ALPHABET = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** @var array<string, array{0: float, 1: ?float, 2: string, 3: string}> stadium name => [hourly, daily, opens, closes] */
    private array $stadiums = [];

    public function __construct()
    {
        foreach (StadiumsSeed::STADIUMS as $row) {
            $this->stadiums[$row[1]] = [$row[5], $row[6], $row[7], $row[8]];
        }
    }

    /** Strong random password: letters and digits, always at least one of each. */
    public static function generatePassword(): string
    {
        do {
            $password = '';
            for ($i = 0; $i < self::PASSWORD_LENGTH; $i++) {
                $password .= self::PASSWORD_ALPHABET[random_int(0, strlen(self::PASSWORD_ALPHABET) - 1)];
            }
        } while (!PasswordHasher::isStrong($password));
        return $password;
    }

    /** @param array<string, string> $passwords username => password */
    public function people(array $passwords): array
    {
        $sql = ["\n-- Demo accounts (ignored if the code, username, email, or phone already exists)"];
        foreach (DemoPeople::PEOPLE as [$code, $role, $name, $username, $email, $phone]) {
            $sql[] = sprintf(
                'INSERT IGNORE INTO users (code, role, full_name, username, email, phone, password_hash, is_active, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, %s, %s, 1, NOW() - INTERVAL 14 DAY, NOW());',
                $this->value($code), $this->value($role), $this->value($name), $this->value($username),
                $this->value($email), $this->value($phone), $this->value(PasswordHasher::hash($passwords[$username]))
            );
        }
        $sql[] = "\n-- Assign the demo stadiums to the demo managers (only stadiums without a manager)";
        foreach (DemoPeople::STADIUM_MANAGERS as $stadium => $managerKey) {
            $sql[] = sprintf(
                'UPDATE stadiums SET manager_id = (SELECT id FROM users WHERE username = %s) WHERE name = %s AND manager_id IS NULL AND deleted_at IS NULL;',
                $this->value(DemoPeople::PEOPLE[$managerKey][3]), $this->value($stadium)
            );
        }
        return $sql;
    }

    public function activity(): array
    {
        $sql = ["\n-- Bookings (relative to today) and their payments"];
        foreach (DemoActivity::RESERVATIONS as $index => $row) {
            [$code, $stadium, $customerKey, $day, $time, $hours, $days, $status, $paid, $method, $reason, $notes] = $row;
            [$start, $end, $unit, $count, $unitPrice, $minutes] = $this->range($stadium, $day, $time, $hours, $days);
            $total = round($unitPrice * $count, 3);
            $paymentStatus = PaymentStatus::fromAmounts($paid, $total, false)->value;
            $blocks = ReservationStatus::from($status)->blocksSlot();
            $sql[] = sprintf(
                "INSERT IGNORE INTO reservations (code, stadium_id, customer_id, start_at, end_at, booking_unit, unit_count, unit_price, duration_minutes, total_amount, paid_amount, status, payment_status, notes, status_reason, created_at, updated_at)\n"
                . "SELECT %s, s.id, u.id, %s, %s, %s, %s, %s, %d, %s, %s, %s, %s, %s, %s, LEAST(NOW(), %s - INTERVAL 2 DAY), NOW()\n"
                . "FROM stadiums s JOIN users u ON u.username = %s WHERE s.name = %s AND s.deleted_at IS NULL%s LIMIT 1;",
                $this->value($code), $start, $end, $this->value($unit), $count, $unitPrice, $minutes, $total, $paid,
                $this->value($status), $this->value($paymentStatus), $this->value($notes), $this->value($reason), $start,
                $this->value(DemoPeople::PEOPLE[$customerKey][3]), $this->value($stadium),
                $blocks ? " AND NOT EXISTS (SELECT 1 FROM reservations o WHERE o.stadium_id = s.id AND o.code <> {$this->value($code)} AND o.deleted_at IS NULL AND o.status IN ('pending', 'confirmed') AND o.start_at < $end AND o.end_at > $start)" : ''
            );
            if ($paid > 0) {
                $sql[] = sprintf(
                    "INSERT IGNORE INTO payments (code, reservation_id, type, method, amount, paid_at, created_at, updated_at, created_by)\n"
                    . "SELECT %s, r.id, 'payment', %s, %s, LEAST(NOW(), r.start_at), NOW(), NOW(), s.manager_id FROM reservations r JOIN stadiums s ON s.id = r.stadium_id WHERE r.code = %s;",
                    $this->value(sprintf('PAY-DEMO%04d', $index + 1)), $this->value($method), $paid, $this->value($code)
                );
            }
        }

        $sql[] = "\n-- Reviews (one per customer and stadium) and the stadium ratings";
        foreach (DemoActivity::REVIEWS as [$code, $stadium, $customerKey, $rating, $comment]) {
            $sql[] = sprintf(
                'INSERT IGNORE INTO reviews (code, stadium_id, customer_id, rating, comment, created_at, updated_at) SELECT %s, s.id, u.id, %d, %s, NOW() - INTERVAL 1 DAY, NOW() FROM stadiums s JOIN users u ON u.username = %s WHERE s.name = %s AND s.deleted_at IS NULL LIMIT 1;',
                $this->value($code), $rating, $this->value($comment), $this->value(DemoPeople::PEOPLE[$customerKey][3]), $this->value($stadium)
            );
        }
        $sql[] = 'UPDATE stadiums s SET s.rating_average = COALESCE((SELECT ROUND(AVG(v.rating), 2) FROM reviews v WHERE v.stadium_id = s.id AND v.deleted_at IS NULL), 0), s.rating_count = (SELECT COUNT(*) FROM reviews v WHERE v.stadium_id = s.id AND v.deleted_at IS NULL) WHERE s.deleted_at IS NULL;';

        $sql[] = "\n-- Expenses";
        foreach (DemoActivity::EXPENSES as [$code, $stadium, $category, $amount, $day, $description]) {
            $sql[] = sprintf(
                'INSERT IGNORE INTO expenses (code, stadium_id, category, amount, expense_date, description, created_at, updated_at, created_by) SELECT %s, s.id, %s, %s, CURDATE() + INTERVAL %d DAY, %s, NOW(), NOW(), s.manager_id FROM stadiums s WHERE s.name = %s AND s.deleted_at IS NULL LIMIT 1;',
                $this->value($code), $this->value($category), $amount, $day, $this->value($description), $this->value($stadium)
            );
        }

        $sql[] = "\n-- Notifications";
        foreach (DemoActivity::NOTIFICATIONS as $index => [$code, $userKey, $template, $title, $body, $relatedType, $related, $isRead]) {
            $relatedCode = $relatedType === 'stadium'
                ? sprintf('(SELECT code FROM stadiums WHERE name = %s AND deleted_at IS NULL LIMIT 1)', $this->value($related))
                : $this->value($related);
            $sql[] = sprintf(
                'INSERT IGNORE INTO notifications (code, user_id, template, title, body, related_type, related_code, read_at, created_at, updated_at) SELECT %s, u.id, %s, %s, %s, %s, %s, %s, NOW() - INTERVAL %d HOUR, NOW() FROM users u WHERE u.username = %s;',
                $this->value($code), $this->value($template), $this->value($title), $this->value($body), $this->value($relatedType),
                $relatedCode, $isRead ? 'NOW()' : 'NULL', ($index + 1) * 3, $this->value(DemoPeople::PEOPLE[$userKey][3])
            );
        }
        return $sql;
    }

    /**
     * SQL start and end expressions plus unit, count, unit price, and minutes for an hourly or daily booking.
     * @return array{0: string, 1: string, 2: string, 3: float, 4: float, 5: int}
     */
    private function range(string $stadium, int $day, ?string $time, float|int|null $hours, ?int $days): array
    {
        [$hourly, $daily, $opens, $closes] = $this->stadiums[$stadium];
        if ($days !== null) {
            $closesNextDay = $closes <= $opens ? 1 : 0;
            $start = sprintf("TIMESTAMP(CURDATE() + INTERVAL %d DAY, '%s:00')", $day, $opens);
            $end = sprintf("TIMESTAMP(CURDATE() + INTERVAL %d DAY, '%s:00')", $day + $days - 1 + $closesNextDay, $closes);
            $minutes = ($days - 1) * 1440 + (int) round((strtotime("2000-01-0" . (1 + $closesNextDay) . " $closes") - strtotime("2000-01-01 $opens")) / 60);
            return [$start, $end, BookingUnit::DAY->value, (float) $days, (float) $daily, $minutes];
        }
        $minutes = (int) round($hours * 60);
        $start = sprintf("TIMESTAMP(CURDATE() + INTERVAL %d DAY, '%s:00')", $day, $time);
        return [$start, "($start + INTERVAL $minutes MINUTE)", BookingUnit::HOUR->value, (float) $hours, (float) $hourly, $minutes];
    }

    private function value(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_int($value), is_float($value) => (string) $value,
            default => "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value) . "'",
        };
    }
}
