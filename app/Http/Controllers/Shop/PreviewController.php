<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SiteMode;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    /** Link segreto di anteprima del sito live (da Admin → Configuración → Lanzamiento). */
    public function enter(Request $request, string $token)
    {
        $valid = (string) Setting::get('live_preview_token', '');
        abort_unless($valid !== '' && hash_equals($valid, $token), 404);
        $request->session()->put('hot_view_mode', 'live');
        return redirect()->route('home')->withCookie(cookie(SiteMode::COOKIE, $token, 60 * 24 * 30, null, null, null, true, false, 'lax'));
    }

    public function leave(Request $request)
    {
        $request->session()->forget('hot_view_mode');
        return redirect()->route('home')->withoutCookie(SiteMode::COOKIE);
    }

    /** Commutatore di vista per chi ha accesso: /vista/live, /vista/early-access */
    public function switch(Request $request, string $mode)
    {
        abort_unless(app(SiteMode::class)->isPrivileged(), 404);
        $request->session()->put('hot_view_mode', $mode === 'live' ? 'live' : 'early_access');
        return redirect()->route('home');
    }
}
