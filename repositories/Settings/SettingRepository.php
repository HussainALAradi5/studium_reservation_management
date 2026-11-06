<?php

declare(strict_types=1);

namespace App\Repositories\Settings;

use App\Constants\Common\CodePrefixes;
use App\Models\Settings\Setting;
use App\Repositories\Base\BaseRepository;

final class SettingRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'settings';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::SETTING;
    }

    /** @return array<string, string> key => stored value */
    public function allValues(): array
    {
        $rows = $this->fetchAllRows('SELECT setting_key, setting_value FROM settings WHERE deleted_at IS NULL');
        return array_column($rows, 'setting_value', 'setting_key');
    }

    public function findByKey(string $key): ?Setting
    {
        $row = $this->fetchOne('SELECT * FROM settings WHERE setting_key = :setting_key AND deleted_at IS NULL', ['setting_key' => $key]);
        return $row ? Setting::fromRow($row) : null;
    }

    public function upsert(string $key, string $value): void
    {
        $existing = $this->findByKey($key);
        if ($existing === null) {
            $this->insert(['setting_key' => $key, 'setting_value' => $value]);
            return;
        }
        if ($existing->value !== $value) {
            $this->update($existing->id, ['setting_value' => $value]);
        }
    }
}
