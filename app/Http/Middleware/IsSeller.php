<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsSeller
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admin can access seller area too
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->isSeller()) {
            abort(403, 'Acceso restringido a vendedores.');
        }

        return $next($request);
    }
}
