<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public const DEFAULTS = [
        'app_name' => 'Vape Stock POS',
        'app_tagline' => 'Vape Retail',
        'logo_path' => '',
        'theme_primary' => '#020617',
        'theme_accent' => '#f59e0b',
        'theme_background' => '#f8fafc',
    ];

    public static function allSettings(): array
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return self::DEFAULTS;
            }
        } catch (\Throwable) {
            return self::DEFAULTS;
        }

        return Cache::rememberForever('app_settings', function (): array {
            $stored = self::query()
                ->pluck('value', 'key')
                ->all();

            return array_replace(self::DEFAULTS, $stored);
        });
    }

    public static function put(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget('app_settings');
    }
}
