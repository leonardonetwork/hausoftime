<?php

namespace App\Models;

use App\Support\Countries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'first_name', 'last_name', 'company', 'tax_id', 'line1', 'line2', 'city', 'postal_code',
        'province', 'country_code', 'phone_prefix', 'phone', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toSnapshot(): array
    {
        return collect($this->only($this->fillable))->except(['user_id', 'label', 'is_default'])->all();
    }

    public static function format(?array $a): string
    {
        if (!$a) return '';
        return collect([
            trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '')),
            $a['company'] ?? null,
            $a['line1'] ?? null,
            $a['line2'] ?? null,
            trim(($a['postal_code'] ?? '') . ' ' . ($a['city'] ?? '')),
            $a['province'] ?? null,
            Countries::name($a['country_code'] ?? null),
            trim(($a['phone_prefix'] ?? '') . ' ' . ($a['phone'] ?? '')),
        ])->filter(fn ($l) => trim((string) $l) !== '')->implode("\n");
    }
}
