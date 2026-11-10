<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $slug = '')
    {
        // $slug può arrivare dal parametro route oppure dal default() della route
        if (empty($slug)) {
            $slug = $request->route('slug') ?? '';
        }

        $page = Page::active()->where('slug', $slug)->first();

        if (!$page) {
            abort(404);
        }

        return view('pages.static', compact('page'));
    }
}
