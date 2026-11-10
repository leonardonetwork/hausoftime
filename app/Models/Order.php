<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending_payment' => ['Pendiente de pago', 'Awaiting payment', 'badge-secondary'],
        'paid'            => ['Pagado',            'Paid',             'badge-gold'],
        'in_progress'     => ['En curso',          'In progress',      'badge-info'],
        'completed'       => ['Completado',        'Completed',        'badge-success'],
        'cancelled'       => ['Cancelado',         'Cancelled',        'badge-danger'],
        'expired'         => ['Caducado',          'Expired',          'badge-secondary'],
        'refunded'        => ['Reembolsado',       'Refunded',         'badge-danger'],
        'disputed'        => ['En disputa',        'Disputed',         'badge-warning'],
    ];

    protected $fillable = [
        'number', 'user_id', 'email', 'status', 'currency', 'subtotal', 'discount_total', 'shipping_total', 'total',
        'coupon_id', 'coupon_code', 'shipping_address', 'billing_address', 'shipping_country', 'customer_notes',
        'admin_notes', 'stripe_session_id', 'stripe_payment_intent', 'stripe_charge_id', 'payment_method',
        'reserved_until', 'paid_at', 'cancelled_at', 'locale', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array', 'billing_address' => 'array',
            'reserved_until' => 'datetime', 'paid_at' => 'datetime', 'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'shipping_total' => 'decimal:2', 'total' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->latest('id');
    }

    public static function nextNumber(): string
    {
        $year = now()->format('Y');
        $last = static::where('number', 'like', "HOT-$year-%")->orderByDesc('id')->value('number');
        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;
        return sprintf('HOT-%s-%05d', $year, $seq);
    }

    public function log(string $type, ?string $message = null, array $meta = [], ?OrderItem $item = null): void
    {
        $this->events()->create([
            'order_item_id' => $item?->id,
            'user_id'       => auth()->id(),
            'type'          => $type,
            'message'       => $message,
            'meta'          => $meta ?: null,
        ]);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        $s = self::STATUSES[$this->status] ?? [$this->status, $this->status];
        return app()->getLocale() === 'en' ? $s[1] : $s[0];
    }

    public function getStatusClassAttribute(): string
    {
        return self::STATUSES[$this->status][2] ?? 'badge-secondary';
    }

    /** Ricalcola lo stato dell'ordine partendo dalle righe. */
    public function refreshStatus(): void
    {
        if (!$this->isPaid()) return;
        $states = $this->items()->pluck('status');
        $status = match (true) {
            $states->contains('disputed')                                            => 'disputed',
            $states->every(fn ($s) => $s === 'refunded')                             => 'refunded',
            $states->every(fn ($s) => in_array($s, ['released', 'refunded'], true))  => 'completed',
            $states->contains(fn ($s) => in_array($s, ['shipped', 'received', 'released'], true)) => 'in_progress',
            default                                                                  => 'paid',
        };
        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }

    public function getShippingAddressTextAttribute(): string
    {
        return Address::format($this->shipping_address);
    }
}
