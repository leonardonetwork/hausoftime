<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'es'));

        if (!in_array($locale, ['es', 'en'])) {
            $locale = 'es';
        }

        app()->setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        return $next($request);
    }
}
