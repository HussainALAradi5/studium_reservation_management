<?php

declare(strict_types=1);

namespace App\Dtos\Reservations;

use App\Validations\Common\Validator;

/** Optional or required reason when rejecting or cancelling. */
final class StatusChangeDto
{
    public function __construct(public readonly ?string $reason)
    {
    }

    public static function fromInput(array $input, bool $isReasonRequired): self
    {
        $data = (new Validator($input))->string('reason', 'Reason', 3, 255, $isReasonRequired)->validated();
        return new self($data['reason']);
    }
}
