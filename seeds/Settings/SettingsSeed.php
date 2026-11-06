<?php

declare(strict_types=1);

namespace App\Seeds\Settings;

use App\Enums\Settings\SettingsKey;
use App\Repositories\Settings\SettingRepository;

/** Stores every setting's default so admins see real rows; existing values are kept. */
final class SettingsSeed
{
    public function run(): void
    {
        $settings = new SettingRepository();
        $stored = $settings->allValues();
        foreach (SettingsKey::cases() as $key) {
            if (!array_key_exists($key->value, $stored)) {
                $settings->upsert($key->value, (string) $key->defaultValue());
            }
        }
        echo "Seeded settings.\n";
    }
}
