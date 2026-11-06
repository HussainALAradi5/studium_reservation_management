<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Config\Database\Database;
use App\Dtos\Settings\SettingsFormDto;
use App\Enums\Settings\SettingsKey;
use App\Repositories\Settings\SettingRepository;

/** Reads settings once per request (with enum defaults as fallback) and saves admin changes. */
final class SettingsService
{
    private static ?array $cache = null;

    public function __construct(private readonly SettingRepository $settings = new SettingRepository())
    {
    }

    public function int(SettingsKey $key): int
    {
        $stored = $this->values()[$key->value] ?? null;
        if ($stored === null || !is_numeric($stored)) {
            return $key->defaultValue();
        }
        [$min, $max] = $key->range();
        return min($max, max($min, (int) $stored));
    }

    public function bool(SettingsKey $key): bool
    {
        return $this->int($key) === 1;
    }

    /** @return array<string, int> key value => effective value, for the settings form */
    public function all(): array
    {
        $result = [];
        foreach (SettingsKey::cases() as $key) {
            $result[$key->value] = $this->int($key);
        }
        return $result;
    }

    public function update(SettingsFormDto $dto): void
    {
        Database::transaction(function () use ($dto) {
            foreach ($dto->values as $key => $value) {
                $this->settings->upsert($key, (string) $value);
            }
        });
        self::$cache = null;
    }

    private function values(): array
    {
        return self::$cache ??= $this->settings->allValues();
    }
}
