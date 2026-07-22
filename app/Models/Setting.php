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
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::rememberForever(
            "setting.{$key}",
            fn () => static::query()->where('key', $key)->first()
        );

        if (! $setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function setValue(
        string $key,
        mixed $value,
        string $group = 'general',
        string $type = 'string'
    ): self {
        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'type' => $type,
                'value' => static::serializeValue($value, $type),
            ]
        );

        Cache::forget("setting.{$key}");
        Cache::forget('settings.all');

        return $setting;
    }

    public static function allAsArray(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()
                ->orderBy('group')
                ->orderBy('key')
                ->get()
                ->mapWithKeys(fn (self $setting) => [
                    $setting->key => static::castValue(
                        $setting->value,
                        $setting->type
                    ),
                ])
                ->all();
        });
    }

    public static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    public static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value === '1',
            'integer' => (int) $value,
            'json' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            default => $value,
        };
    }

    public static function flushSettingsCache(): void
    {
        static::query()->pluck('key')->each(
            fn (string $key) => Cache::forget("setting.{$key}")
        );

        Cache::forget('settings.all');
    }
}
