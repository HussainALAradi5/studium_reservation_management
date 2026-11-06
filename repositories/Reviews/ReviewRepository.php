<?php

declare(strict_types=1);

namespace App\Repositories\Reviews;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Common\PageRequest;
use App\Dtos\Common\PageResult;
use App\Enums\Reservations\ReservationStatus;
use App\Models\Reviews\Review;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class ReviewRepository extends BaseRepository
{
    private const SELECT = 'v.*, u.full_name AS customer_name, s.manager_id AS stadium_manager_id';
    private const FROM = 'FROM reviews v JOIN users u ON u.id = v.customer_id JOIN stadiums s ON s.id = v.stadium_id';
    private const SORT_COLUMNS = ['created' => 'v.created_at', 'rating' => 'v.rating'];

    protected function table(): string
    {
        return 'reviews';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::REVIEW;
    }

    public function findById(int $id): ?Review
    {
        $row = $this->fetchOne('SELECT ' . self::SELECT . ' ' . self::FROM . ' WHERE v.id = :id', ['id' => $id]);
        return $row ? Review::fromRow($row) : null;
    }

    public function findByCode(string $code): ?Review
    {
        $row = $this->fetchOne(
            'SELECT ' . self::SELECT . ' ' . self::FROM . ' WHERE v.code = :code AND v.deleted_at IS NULL',
            ['code' => IdentityNormalizer::code($code)]
        );
        return $row ? Review::fromRow($row) : null;
    }

    /**
     * The customer's review of the stadium, including removed ones: a customer rates a stadium once,
     * even if a moderator later removed the review.
     */
    public function existsForCustomer(int $stadiumId, int $customerId): bool
    {
        return $this->fetchOne(
            'SELECT 1 FROM reviews WHERE stadium_id = :stadium_id AND customer_id = :customer_id LIMIT 1',
            ['stadium_id' => $stadiumId, 'customer_id' => $customerId]
        ) !== null;
    }

    public function paginateForStadium(int $stadiumId, PageRequest $page): PageResult
    {
        return Paginator::paginate(
            $this->pdo,
            self::SELECT,
            self::FROM . ' WHERE v.stadium_id = :stadium_id AND v.deleted_at IS NULL',
            ['stadium_id' => $stadiumId],
            $page,
            self::SORT_COLUMNS,
            static fn (array $row) => Review::fromRow($row)
        );
    }

    /** True when the customer has played at the stadium: a completed booking, or a confirmed one that has ended. */
    public function hasPlayedAt(int $stadiumId, int $customerId): bool
    {
        return $this->fetchOne(
            'SELECT 1 FROM reservations
             WHERE stadium_id = :stadium_id AND customer_id = :customer_id AND deleted_at IS NULL
               AND (status = :completed OR (status = :confirmed AND end_at <= :now))
             LIMIT 1',
            [
                'stadium_id' => $stadiumId,
                'customer_id' => $customerId,
                'completed' => ReservationStatus::COMPLETED->value,
                'confirmed' => ReservationStatus::CONFIRMED->value,
                'now' => self::now(),
            ]
        ) !== null;
    }

    /** Recomputes the stadium's denormalized average and count from visible reviews (call in the same transaction). */
    public function refreshStadiumRating(int $stadiumId): void
    {
        $this->execute(
            'UPDATE stadiums s
             SET s.rating_average = COALESCE((SELECT ROUND(AVG(rating), 2) FROM reviews WHERE stadium_id = :id1 AND deleted_at IS NULL), 0),
                 s.rating_count = (SELECT COUNT(*) FROM reviews WHERE stadium_id = :id2 AND deleted_at IS NULL)
             WHERE s.id = :id3',
            ['id1' => $stadiumId, 'id2' => $stadiumId, 'id3' => $stadiumId]
        );
    }
}
