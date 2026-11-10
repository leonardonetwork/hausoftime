<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderItem extends Model
{
    /** [ES, EN, badge] */
    public const STATUSES = [
        'pending'           => ['Pendiente de pago',     'Awaiting payment',      'badge-secondary'],
        'awaiting_shipment' => ['Pendiente de envío',    'Awaiting shipment',     'badge-gold'],
        'shipped'           => ['Enviado',               'Shipped',               'badge-info'],
        'received'          => ['Recibido',              'Received',              'badge-info'],
        'released'          => ['Fondos liberados',      'Funds released',        'badge-success'],
        'disputed'          => ['Incidencia abierta',    'Issue opened',          'badge-warning'],
        'refunded'          => ['Reembolsado',           'Refunded',              'badge-danger'],
        'cancelled'         => ['Cancelado',             'Cancelled',             'badge-danger'],
    ];

    protected $fillable = [
        'order_id', 'watch_id', 'seller_id', 'status', 'brand', 'model', 'reference', 'image', 'price', 'discount',
        'shipping', 'insurance', 'shipping_rate_name', 'commission_rate', 'commission_amount', 'seller_payout',
        'carrier', 'tracking_number', 'tracking_url', 'ship_by', 'shipped_at', 'received_at', 'release_due_at',
        'released_at', 'stripe_transfer_id', 'refunded_at', 'refunded_amount', 'stripe_refund_id',
        'dispute_opened_at', 'dispute_reason', 'dispute_closed_at', 'last_reminder_at',
    ];

    protected function casts(): array
    {
        return [
            'ship_by' => 'datetime', 'shipped_at' => 'datetime', 'received_at' => 'datetime',
            'release_due_at' => 'datetime', 'released_at' => 'datetime', 'refunded_at' => 'datetime',
            'dispute_opened_at' => 'datetime', 'dispute_closed_at' => 'datetime', 'last_reminder_at' => 'datetime',
            'price' => 'decimal:2', 'discount' => 'decimal:2', 'shipping' => 'decimal:2', 'insurance' => 'decimal:2',
            'commission_rate' => 'decimal:2', 'commission_amount' => 'decimal:2', 'seller_payout' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class)->withTrashed();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getTitleAttribute(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    /** Totale pagato dall'acquirente per questa riga. */
    public function getLineTotalAttribute(): float
    {
        return round((float) $this->price - (float) $this->discount + (float) $this->shipping + (float) $this->insurance, 2);
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

    public function getTrackingLinkAttribute(): ?string
    {
        if ($this->tracking_url) return $this->tracking_url;
        if (!$this->tracking_number) return null;
        return match ($this->carrier) {
            'fedex' => 'https://www.fedex.com/fedextrack/?trknbr=' . urlencode($this->tracking_number),
            'dhl'   => 'https://www.dhl.com/global-en/home/tracking.html?tracking-id=' . urlencode($this->tracking_number),
            'ups'   => 'https://www.ups.com/track?tracknum=' . urlencode($this->tracking_number),
            default => null,
        };
    }

    public function canBeConfirmedByBuyer(): bool
    {
        return $this->status === 'shipped';
    }

    public function canOpenDispute(): bool
    {
        return in_array($this->status, ['awaiting_shipment', 'shipped'], true);
    }
}
