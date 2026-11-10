<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WatchBrand;
use App\Models\WatchCategory;
use Illuminate\Http\Request;

class TranslationsController extends Controller
{
    public function index() {
        $brands=WatchBrand::orderBy('name_es')->get();
        $categories=WatchCategory::orderBy('name_es')->get();
        $languages=['en'=>'English','es'=>'Español'];
        return view('admin.translations',compact('brands','categories','languages'));
    }
    public function translateAll(Request $request) {
        $toLang=$request->input('locale','en'); $count=0;
        WatchBrand::all()->each(function($b) use($toLang,&$count){ $f="name_{$toLang}"; if(empty($b->$f)){ $from=$toLang==='en'?'name_es':'name_en'; $t=BrandsController::aiTranslate($b->$from,$toLang); if($t){$b->update([$f=>$t]);$count++;} } });
        WatchCategory::all()->each(function($c) use($toLang,&$count){ $f="name_{$toLang}"; if(empty($c->$f)){ $from=$toLang==='en'?'name_es':'name_en'; $t=BrandsController::aiTranslate($c->$from,$toLang); if($t){$c->update([$f=>$t]);$count++;} } });
        return back()->with('success',"Traducidos {$count} elementos a ".strtoupper($toLang).'.');
    }
    public function clearTranslations(Request $request) {
        $locale=$request->input('locale','en'); $f="name_{$locale}";
        WatchBrand::query()->update([$f=>null]);
        WatchCategory::query()->update([$f=>null]);
        return back()->with('success','Traducciones eliminadas para '.strtoupper($locale).'.');
    }
}
