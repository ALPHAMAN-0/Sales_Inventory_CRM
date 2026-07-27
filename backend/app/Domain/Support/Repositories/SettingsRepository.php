<?php

namespace App\Domain\Support\Repositories;

use App\Domain\Support\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * The one repository in the app: runtime settings are the one place querying
 * needs to be swappable/cacheable, so it earns an abstraction. Reads the
 * `settings` table (Redis-cached), falling back to a caller-supplied default
 * (which callers source from config/crm.php).
 */
class SettingsRepository
{
    private const TTL = 300;

    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            "settings:{$key}",
            self::TTL,
            fn () => Setting::query()->where('key', $key)->value('value'),
        );

        return $value ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("settings:{$key}");
    }
}
