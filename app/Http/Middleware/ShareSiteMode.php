<?php

namespace App\Http\Middleware;

use App\Services\Cart;
use App\Services\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/** Condivide con tutte le viste lo stato early access / live e il contatore della bolsa. */
class ShareSiteMode
{
    public function handle(Request $request, Closure $next)
    {
        $mode = app(SiteMode::class);
        View::share('siteMode', $mode);
        View::share('shopOpen', $mode->shopOpen());
        View::share('bagCount', $request->hasSession() ? app(Cart::class)->count() : 0);
        return $next($request);
    }
}
