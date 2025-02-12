<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group'
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        try {
            $value = Cache::rememberForever("settings.{$key}", function () use ($key) {
                $setting = self::where('key', $key)->first();
                return $setting ? $setting->value : null;
            });

            return $value ?? $default;
        } catch (\Exception $e) {
            \Log::error("Error getting setting {$key}:", ['error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @return void
     */
    public static function set($key, $value, $group = 'general')
    {
        try {
            \Log::info("Setting {$key}:", ['value' => $value, 'group' => $group]);
            
            $setting = self::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $group
                ]
            );

            Cache::forget("settings.{$key}");
            Cache::forget("settings.group.{$group}");
            
            return $setting;
        } catch (\Exception $e) {
            \Log::error("Error setting {$key}:", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get the logo URL
     *
     * @return string
     */
    public static function getLogoUrl()
    {
        $logo = self::get('app_logo');
        return $logo ? Storage::url($logo) : asset('images/default-logo.png');
    }

    /**
     * Get all settings by group
     *
     * @param string $group
     * @return array
     */
    public static function getGroup($group)
    {
        try {
            return Cache::rememberForever("settings.group.{$group}", function () use ($group) {
                return self::where('group', $group)
                    ->get()
                    ->mapWithKeys(function ($setting) {
                        return [$setting->key => $setting->value];
                    })
                    ->toArray();
            });
        } catch (\Exception $e) {
            \Log::error("Error getting group {$group}:", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache()
    {
        try {
            $settings = self::all();
            foreach ($settings as $setting) {
                Cache::forget("settings.{$setting->key}");
            }
            
            $groups = self::distinct()->pluck('group');
            foreach ($groups as $group) {
                Cache::forget("settings.group.{$group}");
            }
            
            \Log::info('Settings cache cleared');
        } catch (\Exception $e) {
            \Log::error('Error clearing settings cache:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
} 