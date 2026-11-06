<?php

declare(strict_types=1);

namespace App\Dtos\Settings;

use App\Enums\Settings\SettingsKey;
use App\Validations\Common\Validator;

/** Every SettingsKey validated against its own range. */
final class SettingsFormDto
{
    /** @param array<string, int> $values key value => int */
    public function __construct(public readonly array $values)
    {
    }

    public static function fromInput(array $input): self
    {
        $validator = new Validator($input);
        foreach (SettingsKey::cases() as $key) {
            if ($key->isBoolean()) {
                $validator->boolean($key->fieldName());
                continue;
            }
            [$min, $max] = $key->range();
            $validator->integer($key->fieldName(), $key->label(), $min, $max);
        }
        $data = $validator->validated();

        $values = [];
        foreach (SettingsKey::cases() as $key) {
            $values[$key->value] = (int) $data[$key->fieldName()];
        }
        return new self($values);
    }
}
