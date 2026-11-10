<?php

namespace App\Services;

use App\Models\Watch;
use Illuminate\Support\Collection;

/**
 * "Bolsa" di acquisto: ogni orologio è un pezzo unico, quindi si salvano solo gli id in sessione.
 */
class Cart
{
    private const KEY = 'hot_bag';
    public const MAX_ITEMS = 10;

    public function ids(): array
    {
        return array_values(array_unique(array_map('intval', session(self::KEY, []))));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $watchId): bool
    {
        return in_array($watchId, $this->ids(), true);
    }

    public function add(Watch $watch): bool
    {
        $ids = $this->ids();
        if (in_array($watch->id, $ids, true)) return true;
        if (count($ids) >= self::MAX_ITEMS) return false;
        $ids[] = $watch->id;
        session([self::KEY => $ids]);
        return true;
    }

    public function remove(int $watchId): void
    {
        session([self::KEY => array_values(array_diff($this->ids(), [$watchId]))]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function set(array $ids): void
    {
        session([self::KEY => array_values(array_unique(array_map('intval', $ids)))]);
    }

    /**
     * Orologi della bolsa ancora pubblicati. Quelli venduti/ritirati vengono tolti
     * e restituiti in $removed (per avvisare l'utente). Quelli momentaneamente riservati
     * da un altro checkout restano nella bolsa (attributo "reserved_by_other").
     */
    public function watches(?array &$removed = null, ?int $exceptOrderId = null): Collection
    {
        $ids = $this->ids();
        if (!$ids) {
            $removed = [];
            return collect();
        }
        $watches = Watch::with('seller')->whereIn('id', $ids)->get()->keyBy('id');
        $ok = collect();
        $removed = [];
        foreach ($ids as $id) {
            $w = $watches->get($id);
            if ($w && $w->isPublished()) {
                $w->setAttribute('reserved_by_other', $w->isReserved($exceptOrderId));
                $ok->push($w);
            } else {
                $removed[] = $w ? $w->title : '#' . $id;
            }
        }
        if ($removed) $this->set($ok->pluck('id')->all());
        return $ok;
    }
}
