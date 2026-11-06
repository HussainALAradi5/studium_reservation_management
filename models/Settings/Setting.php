<?php

declare(strict_types=1);

namespace App\Models\Settings;

use App\Enums\Settings\SettingsKey;
use App\Models\Base\BaseEntity;

final class Setting extends BaseEntity
{
    public SettingsKey $key;
    public string $value = '';

    public static function fromRow(array $row): static
    {
        $setting = new self();
        $setting->fillBase($row);
        $setting->key = SettingsKey::from($row['setting_key']);
        $setting->value = $row['setting_value'];
        return $setting;
    }
}
