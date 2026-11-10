<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Watch;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $ids = $this->ids($request);
        $watches = Watch::whereIn('id', $ids)->whereIn('status', ['approved', 'sold'])->get()
            ->sortBy(fn ($w) => array_search($w->id, $ids))->values();

        return view('shop.favorites', ['watches' => $watches, 'favoriteIds' => $ids]);
    }

    public function toggle(Request $request, Watch $watch)
    {
        abort_unless($watch->isPublished() || $watch->status === 'sold', 404);

        if ($user = $request->user()) {
            $fav = Favorite::where('user_id', $user->id)->where('watch_id', $watch->id)->first();
            $fav ? $fav->delete() : Favorite::create(['user_id' => $user->id, 'watch_id' => $watch->id]);
            $active = !$fav;
        } else {
            $ids = array_map('intval', $request->session()->get('hot_favorites', []));
            $active = !in_array($watch->id, $ids, true);
            $ids = $active ? array_merge($ids, [$watch->id]) : array_values(array_diff($ids, [$watch->id]));
            $request->session()->put('hot_favorites', array_slice(array_unique($ids), -100));
        }

        if ($request->expectsJson()) {
            return response()->json(['active' => $active, 'count' => count($this->ids($request))]);
        }
        return back();
    }

    /** Dopo il login i preferiti dell'ospite passano all'account. */
    public static function mergeSessionInto($user): void
    {
        $ids = array_map('intval', session('hot_favorites', []));
        foreach ($ids as $id) {
            if (Watch::whereKey($id)->exists()) {
                Favorite::firstOrCreate(['user_id' => $user->id, 'watch_id' => $id]);
            }
        }
        session()->forget('hot_favorites');
    }

    private function ids(Request $request): array
    {
        return $request->user()
            ? $request->user()->favorites()->latest()->pluck('watch_id')->all()
            : array_reverse(array_map('intval', $request->session()->get('hot_favorites', [])));
    }
}
