<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Models\WatchBrand;
use App\Models\WatchCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    /** Home live (pag. 48) e catalogo /relojes con filtri. */
    public function index(Request $request)
    {
        $q = Watch::published()->with('seller:id,country');

        if ($search = trim((string) $request->query('q', ''))) {
            $terms = preg_split('/\s+/', Str::limit($search, 80, ''));
            foreach ($terms as $term) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
                $q->where(fn ($w) => $w->where('brand', 'like', $like)->orWhere('model', 'like', $like)
                    ->orWhere('reference', 'like', $like)->orWhere('category', 'like', $like));
            }
        }

        $brand = null;
        if ($slug = $request->query('marca')) {
            $brand = WatchBrand::where('slug', $slug)->first();
            if ($brand) $q->whereIn('brand', array_filter([$brand->name_es, $brand->name_en]));
        }
        $category = null;
        if ($slug = $request->query('categoria')) {
            $category = WatchCategory::where('slug', $slug)->first();
            if ($category) $q->whereIn('category', array_filter([$category->name_es, $category->name_en]));
        }
        if ($request->boolean('certificado')) {
            $q->where('is_certified', true);
        }

        $sort = $request->query('orden', 'destacados');
        match ($sort) {
            'precio-asc'  => $q->orderByRaw('COALESCE(NULLIF(price_discounted,0), price) asc'),
            'precio-desc' => $q->orderByRaw('COALESCE(NULLIF(price_discounted,0), price) desc'),
            'recientes'   => $q->orderByDesc('approved_at'),
            default       => $q->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('approved_at'),
        };
        $q->orderByDesc('id');

        $watches = $q->paginate(24)->withQueryString();

        if ($request->ajax() && $request->boolean('partial')) {
            return response()->json([
                'html' => view('shop.partials.cards', ['watches' => $watches, 'favoriteIds' => $this->favoriteIds()])->render(),
                'next' => $watches->nextPageUrl(),
            ]);
        }

        // Solo marche/categorie con almeno un orologio pubblicato
        $published = Watch::published()->selectRaw('brand, category')->get();
        $brands = WatchBrand::active()->orderBy('sort_order')->orderBy('name_es')->get()
            ->filter(fn ($b) => $published->contains(fn ($w) => in_array($w->brand, [$b->name_es, $b->name_en], true)))->values();
        $categories = WatchCategory::active()->orderBy('sort_order')->orderBy('name_es')->get()
            ->filter(fn ($c) => $published->contains(fn ($w) => in_array($w->category, [$c->name_es, $c->name_en], true)))->values();

        return view('shop.index', [
            'watches'     => $watches,
            'brands'      => $brands,
            'categories'  => $categories,
            'brand'       => $brand,
            'category'    => $category,
            'search'      => $search,
            'sort'        => $sort,
            'favoriteIds' => $this->favoriteIds(),
            'isHome'      => $request->routeIs('home'),
        ]);
    }

    /** Scheda prodotto (pag. 47). */
    public function show(Request $request, string $slug)
    {
        $watch = Watch::with('seller')->where('slug', $slug)->first();

        if (!$watch) {
            // vecchi link /reloj/{id}
            if (ctype_digit($slug) && ($w = Watch::find($slug)) && $w->slug) {
                return redirect()->route('shop.show', $w->slug, 301);
            }
            abort(404);
        }

        $user = $request->user();
        $canPreview = $user && ($user->isAdmin() || (int) $user->id === (int) $watch->seller_id);
        if (!$watch->isPublished() && $watch->status !== 'sold' && !$canPreview) {
            abort(404);
        }

        if ($watch->isPublished() && !$canPreview) {
            $key = 'viewed_' . $watch->id;
            if (!$request->session()->has($key)) {
                $request->session()->put($key, 1);
                Watch::whereKey($watch->id)->increment('views');
            }
        }

        $brand = WatchBrand::where('name_es', $watch->brand)->orWhere('name_en', $watch->brand)->first();
        $related = Watch::published()->whereKeyNot($watch->id)
            ->where(fn ($q) => $q->where('brand', $watch->brand)->orWhere('category', $watch->category))
            ->orderByDesc('is_featured')->latest('approved_at')->take(6)->get();
        if ($related->count() < 6) {
            $related = $related->concat(Watch::published()->whereKeyNot($watch->id)->whereNotIn('id', $related->pluck('id'))
                ->latest('approved_at')->take(6 - $related->count())->get());
        }

        return view('shop.show', [
            'watch'       => $watch,
            'brand'       => $brand,
            'related'     => $related,
            'favoriteIds' => $this->favoriteIds(),
            'inBag'       => app(\App\Services\Cart::class)->has($watch->id),
            'preview'     => !$watch->isPublished(),
        ]);
    }

    /** Suggerimenti per la barra di ricerca. */
    public function suggest(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) return response()->json([]);
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], Str::limit($term, 60, '')) . '%';

        $items = Watch::published()
            ->where(fn ($w) => $w->where('brand', 'like', $like)->orWhere('model', 'like', $like)->orWhere('reference', 'like', $like))
            ->orderByDesc('is_featured')->latest('approved_at')->take(6)->get()
            ->map(fn (Watch $w) => [
                'title' => $w->title,
                'sub'   => $w->reference ? 'Ref. ' . $w->reference : ($w->card_lines[0] ?? ''),
                'price' => Watch::money($w->effective_price),
                'image' => $w->cover_url,
                'url'   => route('shop.show', $w->slug),
            ]);

        return response()->json($items);
    }

    private function favoriteIds(): array
    {
        if ($u = auth()->user()) return $u->favoriteIds();
        return array_map('intval', session('hot_favorites', []));
    }
}
