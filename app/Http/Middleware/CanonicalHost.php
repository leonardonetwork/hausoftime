<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Tutti gli host (ea.hausoftime.com, www/non-www) → host di APP_URL con 301,
 * mantenendo percorso e query. Disattivato in locale.
 */
class CanonicalHost
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production')) {
            $canonical = parse_url((string) config('app.url'), PHP_URL_HOST);
            $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';
            $wrongHost = $canonical && strcasecmp($request->getHost(), $canonical) !== 0;
            $wrongScheme = $scheme === 'https' && !$request->isSecure();
            if (($wrongHost || $wrongScheme) && !$request->is('stripe/webhook', 'up', '.well-known/*')) {
                return redirect()->away($scheme . '://' . $canonical . $request->getRequestUri(), 301);
            }
        }
        return $next($request);
    }
}
