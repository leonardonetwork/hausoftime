<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) return $default;

        $value = $setting->value;

        if ($setting->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        if ($setting->type === 'json') {
            return json_decode($value, true);
        }

        // encrypted type: try decrypt, fallback to plain
        if ($setting->type === 'encrypted' && $value) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($value);
            } catch (\Exception) {
                return $value; // already plain
            }
        }

        return $value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type]
        );
        Cache::forget("setting_{$key}");
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)->get()->mapWithKeys(function ($s) {
            $value = $s->value;
            if ($s->type === 'encrypted' && $value) {
                try {
                    $value = \Illuminate\Support\Facades\Crypt::decryptString($value);
                } catch (\Exception) {
                    // valore già in chiaro
                }
            }
            return [$s->key => $value];
        })->toArray();
    }
}
