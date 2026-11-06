<?php

declare(strict_types=1);

namespace App\Dtos\Reviews;

use App\Constants\Reviews\ReviewRules;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Validations\Common\Validator;

final class ReviewFormDto
{
    public function __construct(
        public readonly int $rating,
        public readonly string $comment
    ) {
    }

    public static function fromInput(array $input): self
    {
        if (isset($input['comment']) && is_string($input['comment'])) {
            $input['comment'] = IdentityNormalizer::text($input['comment']);
        }
        $data = (new Validator($input))
            ->integer('rating', 'Rating', ReviewRules::MIN_RATING, ReviewRules::MAX_RATING)
            ->string('comment', 'Comment', ReviewRules::MIN_COMMENT, ReviewRules::MAX_COMMENT)
            ->validated();
        return new self($data['rating'], $data['comment']);
    }
}
