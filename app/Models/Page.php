<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug','title_es','title_en','content_es','content_en','active','sort_order'];
    protected function casts(): array { return ['active' => 'boolean']; }

    public function getTitleAttribute(): string {
        return app()->getLocale() === 'en' && $this->title_en ? $this->title_en : $this->title_es;
    }
    public function getContentAttribute(): string {
        return app()->getLocale() === 'en' && $this->content_en ? $this->content_en : ($this->content_es ?? '');
    }
    public function scopeActive($q) { return $q->where('active', true); }
}
