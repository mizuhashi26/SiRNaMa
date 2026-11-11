<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
    ];

    public static function persistentFilePath(): string
    {
        $filename = app()->runningUnitTests() ? 'settings_persistent_test.json' : 'settings_persistent.json';
        return storage_path("app/{$filename}");
    }

    public static function getPersistentSettings(): array
    {
        $path = static::persistentFilePath();
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $decoded = json_decode((string) $content, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    public static function savePersistentSetting(string $key, mixed $value): void
    {
        try {
            $settings = static::getPersistentSettings();
            $settings[$key] = $value;

            $dir = dirname(static::persistentFilePath());
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            file_put_contents(static::persistentFilePath(), json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable) {
            // Fail safely if filesystem is write-restricted
        }
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            try {
                $record = static::where('key', $key)->first();
                if ($record !== null && $record->value !== null && $record->value !== '') {
                    return $record->value;
                }
            } catch (\Throwable) {
                // Table might not exist yet during migration
            }

            // 1. Fallback ke file backup persistent storage
            $persistent = static::getPersistentSettings();
            if (isset($persistent[$key]) && $persistent[$key] !== '' && $persistent[$key] !== null) {
                return $persistent[$key];
            }

            // 2. Fallback ke file .env untuk kredensial API Fonnte (hanya saat bukan running tests)
            if (! app()->runningUnitTests()) {
                if ($key === 'fonnte_token' && filled(env('FONNTE_TOKEN'))) {
                    return env('FONNTE_TOKEN');
                }
                if ($key === 'fonnte_api_url' && filled(env('FONNTE_API_URL'))) {
                    return env('FONNTE_API_URL');
                }
            }

            return $default;
        });
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $label = null): static
    {
        Cache::forget("app_setting_{$key}");

        // Simpan ke database
        $model = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'label' => $label,
            ]
        );

        // Simpan juga ke file persistent backup di storage (aman dari reset database)
        if ($value !== null && $value !== '') {
            static::savePersistentSetting($key, $value);
        }

        return $model;
    }

    public static function restorePersistentToDatabase(): void
    {
        $persistent = static::getPersistentSettings();
        foreach ($persistent as $key => $value) {
            if ($value !== null && $value !== '') {
                static::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }
    }

    public static function clearSettingCache(): void
    {
        Cache::flush();
    }
}

