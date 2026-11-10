<?php

namespace App\Models;

use App\Support\Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Watch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id', 'brand', 'model', 'slug', 'reference', 'case_size', 'material', 'manufacture_year',
        'condition', 'category', 'description', 'description_en', 'price', 'currency',
        'box_papers', 'documentation_files', 'images',
        'status', 'admin_notes', 'approved_at', 'sold_at',
        'price_discounted', 'country_code', 'city',
        'is_certified', 'is_featured', 'sort_order',
        'verification_time', 'verification_photo', 'verification_at',
    ];

    protected function casts(): array
    {
        return [
            'documentation_files' => 'array',
            'images'              => 'array',
            'approved_at'         => 'datetime',
            'verification_at'     => 'datetime',
            'reserved_until'      => 'datetime',
            'sold_at'             => 'datetime',
            'price'               => 'decimal:2',
            'price_discounted'    => 'decimal:2',
            'is_certified'        => 'boolean',
            'is_featured'         => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Watch $w) {
            if (!$w->slug || $w->isDirty(['brand', 'model', 'reference'])) {
                $base = Str::slug(trim("{$w->brand} {$w->model} {$w->reference}")) ?: 'reloj';
                $w->slug = $w->exists ? "{$base}-{$w->id}" : $base . '-' . Str::lower(Str::random(6));
            }
            if (!$w->country_code && $w->seller) {
                $w->country_code = Countries::codeFromName($w->seller->country);
            }
        });
        // Il primo salvataggio non conosce ancora l'id: lo slug definitivo lo usa.
        static::created(function (Watch $w) {
            $base = Str::slug(trim("{$w->brand} {$w->model} {$w->reference}")) ?: 'reloj';
            $w->slug = "{$base}-{$w->id}";
            $w->saveQuietly();
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function brandModel(): BelongsTo
    {
        return $this->belongsTo(WatchBrand::class, 'brand', 'name_es');
    }

    // ── Scopes ────────────────────────────────────────────────────────────
    /** Pubblicati nel negozio (approvati, non venduti). Quelli riservati restano visibili. */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'approved');
    }

    /** Acquistabili adesso (pubblicati e non riservati da un altro checkout). */
    public function scopePurchasable(Builder $q): Builder
    {
        return $q->published()->where(fn ($q) => $q->whereNull('reserved_until')->orWhere('reserved_until', '<', now()));
    }

    // ── Stato ─────────────────────────────────────────────────────────────
    public function isPublished(): bool
    {
        return $this->status === 'approved' && !$this->trashed();
    }

    public function isReserved(?int $exceptOrderId = null): bool
    {
        return $this->reserved_until && $this->reserved_until->isFuture()
            && (!$exceptOrderId || $this->reserved_order_id !== $exceptOrderId);
    }

    public function isPurchasable(): bool
    {
        return $this->isPublished() && !$this->isReserved();
    }

    // ── Prezzo ────────────────────────────────────────────────────────────
    public function getEffectivePriceAttribute(): float
    {
        $d = $this->price_discounted !== null ? (float) $this->price_discounted : null;
        return ($d !== null && $d > 0 && $d < (float) $this->price) ? $d : (float) $this->price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->effective_price < (float) $this->price;
    }

    public function getFormattedPriceAttribute(): string
    {
        return self::money($this->price);
    }

    /** Prezzo per il negozio: "8.000 €" (es) · "€8,000" (en). */
    public static function display($amount): string
    {
        return app()->getLocale() === 'en'
            ? '€' . number_format((float) $amount, 0, '.', ',')
            : number_format((float) $amount, 0, ',', '.') . ' €';
    }

    public static function money($amount, bool $decimals = false): string
    {
        return number_format((float) $amount, $decimals ? 2 : 0, ',', '.') . ' €';
    }

    // ── Presentazione ─────────────────────────────────────────────────────
    public function getTitleAttribute(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])->filter()->map(fn ($p) => Storage::disk('public')->url($p))->values()->all();
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->image_urls[0] ?? null;
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'en' && $this->description_en ? $this->description_en : $this->description;
    }

    public function getLocationLabelAttribute(): ?string
    {
        $country = Countries::name($this->country_code);
        return collect([$this->city, $country])->filter()->implode(', ') ?: null;
    }

    /** Righe brevi della card (dimensione, materiale; altrimenti referenza e anno). */
    public function getCardLinesAttribute(): array
    {
        $lines = array_values(array_filter([$this->case_size, $this->material]));
        if (!$lines) {
            $lines = array_values(array_filter([
                $this->reference ? 'Ref. ' . $this->reference : null,
                $this->manufacture_year ? (string) $this->manufacture_year : null,
            ]));
        }
        return $lines;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'  => ['label' => 'Pendiente', 'class' => 'badge-warning'],
            'approved' => ['label' => 'Aprobado',  'class' => 'badge-success'],
            'rejected' => ['label' => 'Rechazado', 'class' => 'badge-danger'],
            'sold'     => ['label' => 'Vendido',   'class' => 'badge-info'],
            default    => ['label' => 'Borrador',  'class' => 'badge-secondary'],
        };
    }

    public function getConditionLabelAttribute(): string
    {
        $en = app()->getLocale() === 'en';
        return match ($this->condition) {
            'mint'       => $en ? 'Unworn' : 'Sin estrenar',
            'excellent'  => $en ? 'Excellent' : 'Excelente',
            'very_good'  => $en ? 'Very good' : 'Muy bueno',
            'good'       => $en ? 'Good' : 'Bueno',
            'fair'       => $en ? 'Fair' : 'Aceptable',
            default      => (string) $this->condition,
        };
    }

    public function getBoxPapersLabelAttribute(): string
    {
        $en = app()->getLocale() === 'en';
        return match ($this->box_papers) {
            'full_set'    => $en ? 'Full set (box & papers)' : 'Completo (estuche y documentación)',
            'box_only'    => $en ? 'Box only' : 'Solo estuche',
            'papers_only' => $en ? 'Papers only' : 'Solo documentación',
            default       => $en ? 'Watch only' : 'Solo reloj',
        };
    }
}
