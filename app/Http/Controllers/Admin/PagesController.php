<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PagesController extends Controller
{
    public function index() {
        $pages = Page::orderBy('sort_order')->orderBy('title_es')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function create() {
        return view('admin.pages.form');
    }

    public function store(Request $request) {
        $v = $request->validate([
            'title_es'   => 'required|string|max:255',
            'title_en'   => 'nullable|string|max:255',
            'content_es' => 'nullable|string',
            'content_en' => 'nullable|string',
            'slug'       => 'nullable|string|max:100',
            'active'     => 'nullable',
        ]);
        $v['slug']   = Str::slug($v['slug'] ?: $v['title_es']);
        $v['active'] = $request->boolean('active', true);

        // Ensure unique slug
        $base = $v['slug']; $i = 1;
        while (Page::where('slug', $v['slug'])->exists()) $v['slug'] = $base.'-'.$i++;

        // Auto-translate title EN if missing
        if (empty($v['title_en'])) {
            $v['title_en'] = $this->aiTranslate($v['title_es'], 'en');
        }

        Page::create($v);
        return redirect()->route('admin.pages.index')->with('success', 'Página creada correctamente.');
    }

    public function edit(Page $page) {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page) {
        $v = $request->validate([
            'title_es'   => 'required|string|max:255',
            'title_en'   => 'nullable|string|max:255',
            'content_es' => 'nullable|string',
            'content_en' => 'nullable|string',
            'slug'       => 'required|string|max:100',
            'active'     => 'nullable',
        ]);
        $v['active'] = $request->boolean('active', true);

        // Check slug unique (excluding current)
        $newSlug = Str::slug($v['slug']);
        $exists  = Page::where('slug', $newSlug)->where('id', '!=', $page->id)->exists();
        $v['slug'] = $exists ? $newSlug.'-'.$page->id : $newSlug;

        $page->update($v);
        return redirect()->route('admin.pages.index')->with('success', 'Página actualizada.');
    }

    public function destroy(Page $page) {
        $page->delete();
        return back()->with('success', 'Página eliminada.');
    }

    public function translateAi(Request $request, Page $page) {
        $toLang = $request->input('locale', 'en');
        $fromTitle   = $toLang === 'en' ? $page->title_es   : $page->title_en;
        $fromContent = $toLang === 'en' ? $page->content_es : $page->content_en;

        $data = [];
        if ($fromTitle)   $data["title_{$toLang}"]   = $this->aiTranslate($fromTitle, $toLang);
        if ($fromContent) $data["content_{$toLang}"] = $this->aiTranslateHtml($fromContent, $toLang);

        if ($data) { $page->update($data); }
        return back()->with('success', 'Traducción completada a '.strtoupper($toLang).'.');
    }

    private function aiTranslate(string $text, string $toLang): string {
        try {
            $key = Setting::get('openai_key');
            if (!$key) return '';
            $lang = $toLang === 'en' ? 'English' : 'Spanish';
            $r = Http::withToken($key)->timeout(20)->post('https://api.openai.com/v1/chat/completions', [
                'model'    => Setting::get('openai_model', 'gpt-4o'),
                'messages' => [['role'=>'user','content'=>"Translate to {$lang}. Return ONLY the translation: \"{$text}\""]],
                'max_tokens' => 300, 'temperature' => 0.2,
            ]);
            return trim($r->json('choices.0.message.content', ''));
        } catch (\Exception) { return ''; }
    }

    private function aiTranslateHtml(string $html, string $toLang): string {
        try {
            $key = Setting::get('openai_key');
            if (!$key) return '';
            $lang = $toLang === 'en' ? 'English' : 'Spanish';
            $r = Http::withToken($key)->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model'    => Setting::get('openai_model', 'gpt-4o'),
                'messages' => [[
                    'role'    => 'user',
                    'content' => "Translate the following HTML content to {$lang}. Keep all HTML tags intact, translate only the text content. Return ONLY the translated HTML:\n\n{$html}"
                ]],
                'max_tokens' => 4000, 'temperature' => 0.2,
            ]);
            return trim($r->json('choices.0.message.content', ''));
        } catch (\Exception) { return ''; }
    }
}
