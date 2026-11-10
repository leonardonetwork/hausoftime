<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password',
        'role', 'phone_prefix', 'phone', 'country',
        'status', 'is_founding_seller', 'locale', 'city', 'address',
        'stripe_account_id', 'stripe_country', 'stripe_details_submitted', 'stripe_payouts_enabled',
        'marketing_opt_in', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_founding_seller' => 'boolean',
            'stripe_details_submitted' => 'boolean',
            'stripe_payouts_enabled'   => 'boolean',
            'marketing_opt_in'         => 'boolean',
            'last_login_at'            => 'datetime',
        ];
    }

    // --- Helpers ---
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isSeller(): bool   { return $this->role === 'seller'; }
    public function isBuyer(): bool    { return $this->role === 'buyer'; }
    public function isApproved(): bool { return $this->status === 'approved'; }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name;
    }

    // --- Relations ---
    public function watches(): HasMany
    {
        return $this->hasMany(Watch::class, 'seller_id');
    }

    public function earlyAccessRequest(): HasMany
    {
        return $this->hasMany(EarlyAccessRequest::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->latest();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'seller_id');
    }

    // --- Marketplace ---
    public function canSell(): bool
    {
        return $this->isSeller() || $this->isAdmin();
    }

    public function commissionRate(): float
    {
        return $this->is_founding_seller
            ? (float) Setting::get('commission_founding', 4)
            : (float) Setting::get('commission_standard', 7);
    }

    public function hasPayoutsReady(): bool
    {
        return $this->stripe_account_id && $this->stripe_payouts_enabled;
    }

    public function favoriteIds(): array
    {
        return $this->favorites()->pluck('watch_id')->all();
    }
}
