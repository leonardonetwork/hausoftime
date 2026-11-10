<?php

namespace App\Http\Middleware;

use App\Services\SiteMode;
use Closure;
use Illuminate\Http\Request;

/** In early access il negozio esiste solo per admin, whitelist e link di anteprima. */
class EnsureShopOpen
{
    public function handle(Request $request, Closure $next)
    {
        if (!app(SiteMode::class)->shopOpen()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not available'], 404);
            }
            return redirect()->route('home');
        }
        return $next($request);
    }
}
