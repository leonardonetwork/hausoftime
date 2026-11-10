<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WatchBrand;
use App\Models\WatchCategory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

// ════════════════════════════════════════════════════
// BRANDS
// ════════════════════════════════════════════════════
class BrandsController extends Controller
{
    public function index()
    {
        $brands = WatchBrand::orderBy('sort_order')->orderBy('name_es')->get();
        return view('admin.catalog.brands', compact('brands'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name_es'    => 'required|string|max:200',
            'name_en'    => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($v['name_es']);
        $base = $slug; $i = 1;
        while (WatchBrand::where('slug', $slug)->exists()) $slug = $base.'-'.$i++;

        $brand = WatchBrand::create([...$v, 'slug' => $slug, 'active' => true]);

        // Auto-translate EN if missing
        if (empty($brand->name_en)) {
            $brand->name_en = self::translateWithAI($brand->name_es, 'en');
            $brand->save();
        }

        return back()->with('success', 'Marca creada correctamente.');
    }

    public function update(Request $request, WatchBrand $brand)
    {
        $v = $request->validate([
            'name_es'    => 'required|string|max:200',
            'name_en'    => 'nullable|string|max:200',
            'active'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $v['active'] = $request->boolean('active');
        $brand->update($v);
        return back()->with('success', 'Marca actualizada.');
    }

    public function destroy(WatchBrand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Marca eliminada.');
    }

    public static function translateWithAI(string $text, string $toLang): string
    {
        try {
            $key   = Setting::get('openai_key');
            $model = Setting::get('openai_model', 'gpt-4o');
            if (!$key) return '';

            $langName = $toLang === 'en' ? 'English' : 'Spanish';
            $response = Http::withToken($key)->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model'       => $model,
                'messages'    => [[
                    'role'    => 'user',
                    'content' => "Translate this watch brand/category name to {$langName}. Return ONLY the translation, nothing else: \"{$text}\""
                ]],
                'max_tokens'  => 60,
                'temperature' => 0.1,
            ]);
            return trim($response->json('choices.0.message.content', $text));
        } catch (\Exception) {
            return '';
        }
    }
}

// ════════════════════════════════════════════════════
// CATEGORIES
// ════════════════════════════════════════════════════
class CategoriesController extends Controller
{
    public function index()
    {
        $categories = WatchCategory::orderBy('sort_order')->orderBy('name_es')->get();
        return view('admin.catalog.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name_es'    => 'required|string|max:200',
            'name_en'    => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = Str::slug($v['name_es']);
        $base = $slug; $i = 1;
        while (WatchCategory::where('slug', $slug)->exists()) $slug = $base.'-'.$i++;

        $cat = WatchCategory::create([...$v, 'slug' => $slug, 'active' => true]);

        if (empty($cat->name_en)) {
            $cat->name_en = BrandsController::translateWithAI($cat->name_es, 'en');
            $cat->save();
        }

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, WatchCategory $category)
    {
        $v = $request->validate([
            'name_es'    => 'required|string|max:200',
            'name_en'    => 'nullable|string|max:200',
            'active'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $v['active'] = $request->boolean('active');
        $category->update($v);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(WatchCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}

// ════════════════════════════════════════════════════
// SELLERS (Vendedores)
// ════════════════════════════════════════════════════
class SellersController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\User::where('role', 'seller')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
            );
        }

        $sellers = $query->paginate(25)->withQueryString();
        $counts  = [
            'all'      => \App\Models\User::where('role','seller')->count(),
            'pending'  => \App\Models\User::where('role','seller')->where('status','pending')->count(),
            'approved' => \App\Models\User::where('role','seller')->where('status','approved')->count(),
            'rejected' => \App\Models\User::where('role','seller')->where('status','rejected')->count(),
        ];

        return view('admin.sellers.index', compact('sellers', 'counts'));
    }

    public function approve(\App\Models\User $user)
    {
        $user->update(['status' => 'approved']);
        return back()->with('success', 'Vendedor aprobado.');
    }

    public function reject(\App\Models\User $user)
    {
        $user->update(['status' => 'rejected']);
        return back()->with('success', 'Vendedor rechazado.');
    }
}

// ════════════════════════════════════════════════════
// TRANSLATIONS (AI)
// ════════════════════════════════════════════════════
class TranslationsController extends Controller
{
    public function index()
    {
        $brands     = WatchBrand::orderBy('name_es')->get();
        $categories = WatchCategory::orderBy('name_es')->get();
        $languages  = ['en' => 'English', 'es' => 'Español'];
        return view('admin.translations', compact('brands', 'categories', 'languages'));
    }

    public function translateAll(Request $request)
    {
        $toLang = $request->input('locale', 'en');
        $count  = 0;

        // Brands missing translation
        WatchBrand::all()->each(function ($brand) use ($toLang, &$count) {
            $field = "name_{$toLang}";
            if (empty($brand->$field)) {
                $fromField  = $toLang === 'en' ? 'name_es' : 'name_en';
                $translated = BrandsController::translateWithAI($brand->$fromField, $toLang);
                if ($translated) { $brand->update([$field => $translated]); $count++; }
            }
        });

        // Categories missing translation
        WatchCategory::all()->each(function ($cat) use ($toLang, &$count) {
            $field = "name_{$toLang}";
            if (empty($cat->$field)) {
                $fromField  = $toLang === 'en' ? 'name_es' : 'name_en';
                $translated = BrandsController::translateWithAI($cat->$fromField, $toLang);
                if ($translated) { $cat->update([$field => $translated]); $count++; }
            }
        });

        return back()->with('success', "Traducidos {$count} elementos a " . strtoupper($toLang) . '.');
    }

    public function clearTranslations(Request $request)
    {
        $locale = $request->input('locale', 'en');
        $field  = "name_{$locale}";
        WatchBrand::query()->update([$field => null]);
        WatchCategory::query()->update([$field => null]);
        return back()->with('success', 'Traducciones eliminadas para ' . strtoupper($locale) . '.');
    }
}
