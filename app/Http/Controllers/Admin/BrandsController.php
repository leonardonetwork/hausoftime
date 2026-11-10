<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WatchBrand;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class BrandsController extends Controller
{
    public function index() {
        $brands = WatchBrand::orderBy('sort_order')->orderBy('name_es')->get();
        return view('admin.catalog.brands', compact('brands'));
    }
    public function store(Request $request) {
        $v = $request->validate(['name_es'=>'required|string|max:200','name_en'=>'nullable|string|max:200','sort_order'=>'nullable|integer']);
        $slug = Str::slug($v['name_es']); $base=$slug; $i=1;
        while(WatchBrand::where('slug',$slug)->exists()) $slug=$base.'-'.$i++;
        $brand = WatchBrand::create([...$v,'slug'=>$slug,'active'=>true]);
        if(empty($brand->name_en)) { $brand->name_en=self::aiTranslate($brand->name_es,'en'); $brand->save(); }
        return back()->with('success','Marca creada.');
    }
    public function update(Request $request, WatchBrand $brand) {
        $v = $request->validate(['name_es'=>'required|string|max:200','name_en'=>'nullable|string|max:200','active'=>'nullable','sort_order'=>'nullable|integer',
                                 'logo'=>'nullable|image|mimes:png,webp|max:2048']);
        $v['active']=$request->boolean('active');
        unset($v['logo']);
        // Logo opzionale (PNG/WebP trasparente; niente SVG per sicurezza) mostrato in alto a sinistra nelle card della tienda
        if ($request->hasFile('logo')) {
            if ($brand->logo) \Illuminate\Support\Facades\Storage::disk('public')->delete($brand->logo);
            $v['logo'] = $request->file('logo')->store('brands', 'public');
        } elseif ($request->boolean('remove_logo') && $brand->logo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($brand->logo);
            $v['logo'] = null;
        }
        $brand->update($v);
        return back()->with('success','Marca actualizada.');
    }
    public function destroy(WatchBrand $brand) {
        $brand->delete();
        return back()->with('success','Marca eliminada.');
    }
    public static function aiTranslate(string $text, string $toLang): string {
        $lang=$toLang==='en'?'English':'Spanish (Spain)';
        return \App\Services\AiClient::stripQuotes(\App\Services\AiClient::ask("Translate this watch brand/category name to {$lang}. Return ONLY the translation: \"{$text}\"", 60, 30));
    }
}
