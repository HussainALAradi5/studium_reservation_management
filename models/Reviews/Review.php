<?php

declare(strict_types=1);

namespace App\Models\Reviews;

use App\Models\Base\BaseEntity;

/** One customer's rating and comment for a stadium they rented (at most one per customer and stadium). */
final class Review extends BaseEntity
{
    public int $stadiumId = 0;
    public int $customerId = 0;
    public int $rating = 0;
    public string $comment = '';

    // Joined columns
    public string $customerName = '';
    public ?int $stadiumManagerId = null;

    public static function fromRow(array $row): static
    {
        $review = new self();
        $review->fillBase($row);
        $review->stadiumId = (int) $row['stadium_id'];
        $review->customerId = (int) $row['customer_id'];
        $review->rating = (int) $row['rating'];
        $review->comment = $row['comment'];
        $review->customerName = (string) ($row['customer_name'] ?? '');
        $review->stadiumManagerId = self::nullableInt($row['stadium_manager_id'] ?? null);
        return $review;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'customerName' => $this->customerName,
            'createdAt' => $this->createdAt,
        ];
    }
}
