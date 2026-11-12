<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Models\WatchBrand;
use App\Models\WatchCategory;

/** Home alternative da mostrare al cliente: /home-2 (Galería) e /home-3 (Editorial). */
class AltHomeController extends Controller
{
    public function show(int $variant)
    {
        $published = Watch::published()->get();

        $brands = WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get()
            ->map(function ($b) use ($published) {
                $items = $published->filter(fn ($w) => in_array($w->brand, array_filter([$b->name_es, $b->name_en]), true));
                $b->pieces = $items->count();
                $b->cover = optional($items->sortByDesc('is_featured')->first())->cover_url;
                $b->from = $items->min('effective_price');
                return $b;
            })->filter(fn ($b) => $b->pieces > 0)->values();

        $categories = WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get()
            ->map(function ($c) use ($published) {
                $items = $published->filter(fn ($w) => in_array($w->category, array_filter([$c->name_es, $c->name_en]), true));
                $c->pieces = $items->count();
                $c->covers = $items->sortByDesc('is_featured')->take(3)->map->cover_url->filter()->values();
                return $c;
            })->filter(fn ($c) => $c->pieces > 0)->values();

        $featured = $published->sortByDesc(fn ($w) => [$w->is_featured, $w->is_certified, $w->effective_price])->values();
        $latest = $published->sortByDesc('approved_at')->values();

        $brandMap = [];
        foreach (WatchBrand::all() as $b) {
            $brandMap[$b->name_es] = $b;
            if ($b->name_en) $brandMap[$b->name_en] = $b;
        }

        return view("shop.home-{$variant}", [
            'brands'      => $brands,
            'categories'  => $categories,
            'featured'    => $featured,
            'latest'      => $latest,
            'hero'        => $featured->first(fn ($w) => $w->cover_url),
            'brandMap'    => $brandMap,
            'favoriteIds' => $this->favoriteIds(),
            'stats'       => [
                'pieces'    => $published->count(),
                'certified' => $published->where('is_certified', true)->count(),
                'brands'    => $brands->count(),
                'countries' => $published->pluck('country_code')->filter()->unique()->count(),
            ],
        ]);
    }

    private function favoriteIds(): array
    {
        if ($u = auth()->user()) return $u->favoriteIds();
        return array_map('intval', session('hot_favorites', []));
    }
}
