<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        try {
            $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
                return self::where('key', $key)->first();
            });
        } catch (\Throwable $e) {
            return $default;
        }

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $type = 'text', string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("setting_{$key}");
        Cache::forget('all_settings');
    }

    /**
     * Get all settings as array.
     */
    public static function getAllSettings(): array
    {
        try {
            return Cache::remember('all_settings', 3600, function () {
                $settings = [];
                foreach (self::all() as $setting) {
                    $settings[$setting->key] = self::castValue($setting->value, $setting->type);
                }
                return $settings;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get settings by group.
     */
    public static function getByGroup(string $group): array
    {
        try {
            return Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
                $settings = [];
                foreach (self::where('group', $group)->get() as $setting) {
                    $settings[$setting->key] = self::castValue($setting->value, $setting->type);
                }
                return $settings;
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('all_settings');
        
        foreach (self::all() as $setting) {
            Cache::forget("setting_{$setting->key}");
        }

        $groups = self::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings_group_{$group}");
        }
    }
}
