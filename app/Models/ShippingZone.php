<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'notice_es', 'notice_en', 'active', 'sort_order'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->orderBy('sort_order');
    }

    public function getNoticeAttribute(): ?string
    {
        return app()->getLocale() === 'en' ? ($this->notice_en ?: $this->notice_es) : $this->notice_es;
    }
}
