<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model
{
    protected $fillable = ['code', 'name_es', 'name_en', 'enabled', 'shipping_zone_id', 'sort_order'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_es;
    }

    public function scopeEnabled($q)
    {
        return $q->where('enabled', true);
    }

    /** Paesi di spedizione abilitati, ordinati per nome nella lingua corrente. */
    public static function shippable()
    {
        $col = app()->getLocale() === 'en' ? 'name_en' : 'name_es';
        return static::enabled()->whereHas('zone', fn ($q) => $q->where('active', true))
            ->orderBy('sort_order')->orderBy($col)->get();
    }
}
