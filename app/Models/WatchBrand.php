<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WatchBrand extends Model
{
    protected $fillable = ['name_es','name_en','slug','logo','active','sort_order'];
    protected function casts(): array { return ['active' => 'boolean']; }

    protected static function booted(): void {
        static::creating(fn($m) => $m->slug ??= Str::slug($m->name_es));
    }
    public function getNameAttribute(): string {
        return app()->getLocale() === 'en' && $this->name_en ? $this->name_en : $this->name_es;
    }
    public function scopeActive($q) { return $q->where('active', true); }
    public function getLogoUrlAttribute(): ?string {
        return $this->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo) : null;
    }
}
