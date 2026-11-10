<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'list_id', 'email', 'name', 'type', 'locale',
        'unsubscribe_token', 'subscribed_at', 'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at'   => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->unsubscribe_token) {
                $model->unsubscribe_token = Str::random(64);
            }
            if (!$model->subscribed_at) {
                $model->subscribed_at = now();
            }
        });
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(NewsletterList::class, 'list_id');
    }

    public function isActive(): bool
    {
        return is_null($this->unsubscribed_at);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }

    /**
     * Subscribe an email to a list — idempotent (no duplicates).
     */
    public static function subscribe(
        string $email,
        ?string $name = null,
        ?int $listId = null,
        string $type = 'general',
        string $locale = 'es'
    ): static {
        return static::firstOrCreate(
            ['email' => $email, 'list_id' => $listId],
            [
                'name'   => $name,
                'type'   => $type,
                'locale' => $locale,
            ]
        );
    }
}
