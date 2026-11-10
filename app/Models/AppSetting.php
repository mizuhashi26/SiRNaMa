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

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting_{$key}", 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if ($record === null || $record->value === null || $record->value === '') {
                return $default;
            }
            return $record->value;
        });
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $label = null): static
    {
        Cache::forget("app_setting_{$key}");

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'label' => $label,
            ]
        );
    }

    public static function clearSettingCache(): void
    {
        Cache::flush();
    }
}

