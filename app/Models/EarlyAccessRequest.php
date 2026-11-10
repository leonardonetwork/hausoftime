<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarlyAccessRequest extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_prefix', 'phone',
        'country', 'city', 'type', 'watch_brands', 'watch_count',
        'estimated_value', 'additional_info', 'status',
        'user_id', 'ip_address', 'locale', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'watch_brands' => 'array',
            'approved_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isSeller(): bool { return $this->type === 'seller'; }
    public function isBuyer(): bool  { return $this->type === 'buyer'; }
}
