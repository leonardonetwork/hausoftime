<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsletterList extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(NewsletterSubscriber::class, 'list_id');
    }

    public function activeSubscribers(): HasMany
    {
        return $this->hasMany(NewsletterSubscriber::class, 'list_id')->whereNull('unsubscribed_at');
    }

    public function getActiveCountAttribute(): int
    {
        return $this->activeSubscribers()->count();
    }
}
