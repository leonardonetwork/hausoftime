<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'name_es', 'name_en', 'carrier', 'base_price', 'insurance_rate', 'insurance_min',
        'free_over', 'min_value', 'max_value', 'delivery_estimate', 'active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean', 'base_price' => 'decimal:2', 'insurance_rate' => 'decimal:3',
            'insurance_min' => 'decimal:2', 'free_over' => 'decimal:2', 'min_value' => 'decimal:2', 'max_value' => 'decimal:2',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name_es;
    }

    public function appliesTo(float $value): bool
    {
        return $this->active
            && ($this->min_value === null || $value >= (float) $this->min_value)
            && ($this->max_value === null || $value <= (float) $this->max_value);
    }

    /** Costo per un pezzo del valore indicato: [porte, seguro]. */
    public function quote(float $value): array
    {
        if ($this->free_over !== null && $value >= (float) $this->free_over) {
            return [0.0, 0.0];
        }
        $insurance = max((float) $this->insurance_min, round($value * (float) $this->insurance_rate / 100, 2));
        return [round((float) $this->base_price, 2), round($insurance, 2)];
    }
}
