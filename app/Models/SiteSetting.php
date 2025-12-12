<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description'];

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected static int $cacheTtl = 3600;

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("site_setting_{$key}", static::$cacheTtl, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        // Convert boolean to string for storage
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $setting->update(['value' => (string) $value]);

        // Clear cache
        Cache::forget("site_setting_{$key}");

        return true;
    }

    /**
     * Cast value to appropriate type
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) (int) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)->get()->toArray();
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = static::pluck('key')->toArray();
        foreach ($keys as $key) {
            Cache::forget("site_setting_{$key}");
        }
    }

    // ============================================================
    // HELPER METHODS - Shortcuts for commonly used settings
    // ============================================================

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceEnabled(): bool
    {
        return (bool) static::get('maintenance_mode', false);
    }

    /**
     * Check if registration is enabled
     */
    public static function isRegistrationEnabled(): bool
    {
        return (bool) static::get('registration_enabled', true);
    }

    /**
     * Check if OTP WhatsApp is enabled
     */
    public static function isOtpEnabled(): bool
    {
        return (bool) static::get('otp_enabled', false);
    }

    /**
     * Check if WhatsApp notifications are enabled
     */
    public static function isWaNotificationEnabled(): bool
    {
        return (bool) static::get('wa_notification_enabled', true);
    }

    /**
     * Check if Basic Listening quiz is enabled
     */
    public static function isBlQuizEnabled(): bool
    {
        return (bool) static::get('bl_quiz_enabled', true);
    }
}
