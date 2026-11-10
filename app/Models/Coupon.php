<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_subtotal', 'max_discount', 'brand_ids', 'first_order_only',
        'free_shipping', 'starts_at', 'ends_at', 'usage_limit', 'usage_limit_per_user', 'used_count', 'active',
    ];

    protected function casts(): array
    {
        return [
            'brand_ids' => 'array', 'first_order_only' => 'boolean', 'free_shipping' => 'boolean', 'active' => 'boolean',
            'starts_at' => 'datetime', 'ends_at' => 'datetime', 'value' => 'decimal:2',
            'min_subtotal' => 'decimal:2', 'max_discount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (Coupon $c) => $c->code = strtoupper(trim($c->code)));
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public static function findByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));
        return $code === '' ? null : static::where('code', $code)->first();
    }

    /**
     * Verifica il coupon per il carrello. Ritorna null se valido, altrimenti il messaggio d'errore.
     * @param Collection<int,Watch> $watches
     */
    public function validationError(Collection $watches, ?string $email, ?User $user): ?string
    {
        $t = fn ($k) => __("shop.coupon.$k");
        if (!$this->active) return $t('invalid');
        if ($this->starts_at && $this->starts_at->isFuture()) return $t('not_started');
        if ($this->ends_at && $this->ends_at->isPast()) return $t('expired');
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return $t('exhausted');

        $eligible = $this->eligibleSubtotal($watches);
        if ($eligible <= 0) return $t('not_applicable');
        if ($this->min_subtotal !== null && $watches->sum('effective_price') < (float) $this->min_subtotal) {
            return __('shop.coupon.min_subtotal', ['amount' => Watch::money($this->min_subtotal)]);
        }

        $identity = fn ($q) => $q->where(function ($q) use ($email, $user) {
            if ($user) $q->where('user_id', $user->id);
            if ($email) $q->orWhere('email', $email);
        });
        if (($user || $email) && $this->usage_limit_per_user !== null) {
            if ($identity($this->redemptions())->count() >= $this->usage_limit_per_user) return $t('already_used');
        }
        if ($this->first_order_only && ($user || $email)) {
            $prev = Order::whereNotNull('paid_at')->where(function ($q) use ($email, $user) {
                if ($user) $q->where('user_id', $user->id);
                if ($email) $q->orWhere('email', $email);
            })->exists();
            if ($prev) return $t('first_order');
        }
        return null;
    }

    public function appliesToWatch(Watch $w): bool
    {
        if (empty($this->brand_ids)) return true;
        $brandId = WatchBrand::where('name_es', $w->brand)->orWhere('name_en', $w->brand)->value('id');
        return $brandId && in_array($brandId, array_map('intval', $this->brand_ids), true);
    }

    public function eligibleSubtotal(Collection $watches): float
    {
        return (float) $watches->filter(fn ($w) => $this->appliesToWatch($w))->sum('effective_price');
    }

    /** Sconto totale (prima della ripartizione sulle righe). */
    public function discountFor(Collection $watches): float
    {
        $base = $this->eligibleSubtotal($watches);
        $d = $this->type === 'percent' ? $base * (float) $this->value / 100 : (float) $this->value;
        if ($this->max_discount !== null) $d = min($d, (float) $this->max_discount);
        return round(min($d, $base), 2);
    }

    public function getSummaryAttribute(): string
    {
        $v = $this->type === 'percent' ? rtrim(rtrim(number_format((float) $this->value, 2, ',', '.'), '0'), ',') . ' %' : Watch::money($this->value, true);
        return $v . ($this->free_shipping ? ' + envío gratis' : '');
    }
}
