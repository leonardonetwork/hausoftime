<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Shop\ShopController;
use App\Services\SiteMode;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Early access → landing attuale (invariata).
     * Live (o anteprima per admin/whitelist) → catalogo del marketplace (pag. 48).
     */
    public function index(Request $request, SiteMode $mode)
    {
        if ($mode->showsLive()) {
            return app(ShopController::class)->index($request);
        }
        return view('pages.home');
    }

    /** La landing Founding Sellers resta disponibile su /vender anche a sito live. */
    public function sell()
    {
        return view('pages.home');
    }

    public function setLocale(Request $request, string $locale)
    {
        if (in_array($locale, ['es', 'en'])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            if ($user = $request->user()) {
                $user->forceFill(['locale' => $locale])->saveQuietly();
            }
        }
        return redirect()->back();
    }
}
