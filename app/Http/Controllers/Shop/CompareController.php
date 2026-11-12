<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Models\WatchBrand;
use Illuminate\Http\Request;

/** Comparador: hasta 4 relojes guardados en sesión. */
class CompareController extends Controller
{
    public const KEY = 'hot_compare';
    public const MAX = 4;

    public static function ids(): array
    {
        return array_values(array_map('intval', session(self::KEY, [])));
    }

    public function index(Request $request)
    {
        $ids = self::ids();
        $watches = Watch::whereIn('id', $ids)->whereIn('status', ['approved', 'sold'])->get()
            ->filter(fn ($w) => $w->isPublished() || $w->status === 'sold')
            ->sortBy(fn ($w) => array_search($w->id, $ids))->values();

        // ripulisce la sessione da relojes non più disponibili
        if ($watches->count() !== count($ids)) {
            $request->session()->put(self::KEY, $watches->pluck('id')->all());
        }

        $brands = WatchBrand::whereIn('name_es', $watches->pluck('brand'))->orWhereIn('name_en', $watches->pluck('brand'))->get();
        $brandMap = [];
        foreach ($brands as $b) {
            $brandMap[$b->name_es] = $b;
            if ($b->name_en) $brandMap[$b->name_en] = $b;
        }

        return view('shop.compare', ['watches' => $watches, 'brandMap' => $brandMap]);
    }

    public function toggle(Request $request, Watch $watch)
    {
        abort_unless($watch->isPublished() || $watch->status === 'sold', 404);

        $ids = self::ids();
        $full = false;
        if (in_array($watch->id, $ids, true)) {
            $ids = array_values(array_diff($ids, [$watch->id]));
            $active = false;
        } elseif (count($ids) >= self::MAX) {
            $active = false;
            $full = true;
        } else {
            $ids[] = $watch->id;
            $active = true;
        }
        $request->session()->put(self::KEY, $ids);

        if ($request->expectsJson()) {
            return response()->json(['active' => $active, 'full' => $full, 'count' => count($ids), 'max' => self::MAX]);
        }
        return $full ? back()->with('error', __('shop.compare.full', ['max' => self::MAX])) : back();
    }

    public function clear(Request $request)
    {
        $request->session()->forget(self::KEY);
        return redirect()->route('compare.index');
    }
}
